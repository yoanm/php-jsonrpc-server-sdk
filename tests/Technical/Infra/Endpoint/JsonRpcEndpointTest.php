<?php
namespace Tests\Technical\Infra\Endpoint;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\RequestHandler;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawRequest;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawResponse;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint
 */
class JsonRpcEndpointTest extends TestCase
{
    /** @var JsonRpcEndpoint */
    private $endpoint;
    /** @var RawRequestSerializer|ObjectProphecy */
    private $rawRequestSerializer;
    /** @var RequestHandler|ObjectProphecy */
    private $requestHandler;
    /** @var ResponseCreator|ObjectProphecy */
    private $responseCreator;
    /** @var RawResponseSerializer|ObjectProphecy */
    private $rawResponseNormalizer;

    public function setUp()
    {
        $this->rawRequestSerializer = $this->prophesize(RawRequestSerializer::class);
        $this->requestHandler = $this->prophesize(RequestHandler::class);
        $this->responseCreator = $this->prophesize(ResponseCreator::class);
        $this->rawResponseNormalizer = $this->prophesize(RawResponseSerializer::class);

        $this->endpoint = new JsonRpcEndpoint(
            $this->rawRequestSerializer->reveal(),
            $this->requestHandler->reveal(),
            $this->rawResponseNormalizer->reveal(),
            $this->responseCreator->reveal()
        );
    }

    /**
     * @dataProvider provideHandleExceptionData
     * @param $exceptionClass
     */
    public function testShouldManageAnyExceptionThrownBeforeResponseSerialization($exceptionClass)
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem */
        $fakeRequestItem = $this->prophesize(JsonRpcRequest::class);
        $fakeHandleException = $this->prophesize($exceptionClass);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeResponseItem = $this->prophesize(JsonRpcResponse::class);
        /** @var ObjectProphecy|JsonRpcRawRequest $jsonRpcRawRequest */
        $jsonRpcRawRequest = $this->prophesize(JsonRpcRawRequest::class);

        $jsonRpcRawRequest->isBatch()->willReturn(false)->shouldBeCalled();

        $this->rawRequestSerializer->deserialize($requestString)
            ->willReturn($jsonRpcRawRequest->reveal())
            ->shouldBeCalled();

        $jsonRpcRawRequest->getItemtList()
            ->willReturn([$fakeRequestItem->reveal()])
            ->shouldBeCalled();

        $this->requestHandler->handle($fakeRequestItem)
            ->willThrow($fakeHandleException->reveal())
            ->shouldBeCalled();

        $this->responseCreator->createErrorResponse(
            JsonRpcException::class === $exceptionClass
                ? $fakeHandleException
                : Argument::type(JsonRpcInternalErrorException::class)
        )
            ->willReturn($fakeResponseItem->reveal())
            ->shouldBeCalled();

        $this->rawResponseNormalizer->serialize(Argument::allOf(
            Argument::type(JsonRpcRawResponse::class),
            Argument::which('getResponseList', [$fakeResponseItem->reveal()])
        ))
            ->willReturn($expectedResponseString)
            ->shouldBeCalled();

        $this->assertSame(
            $expectedResponseString,
            $this->endpoint->index($requestString)
        );
    }

    public function provideHandleExceptionData()
    {
        return [
            'default' => [
                'exceptionClass' => \Exception::class,
            ],
            'JsonRpcException' => [
                'exceptionClass' => JsonRpcException::class,
            ],
        ];
    }
}
