<?php
namespace Tests\Technical\Infra\Endpoint;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint
 */
class JsonRpcEndpointTest extends TestCase
{
    /** @var JsonRpcEndpoint */
    private $endpoint;
    /** @var JsonRpcCallSerializer|ObjectProphecy */
    private $jsonRpcCallSerializer;
    /** @var JsonRpcRequestHandler|ObjectProphecy */
    private $jsonRpcRequestHandler;
    /** @var ExceptionHandler|ObjectProphecy */
    private $exceptionHandler;

    public function setUp()
    {
        $this->jsonRpcCallSerializer = $this->prophesize(JsonRpcCallSerializer::class);
        $this->jsonRpcRequestHandler = $this->prophesize(JsonRpcRequestHandler::class);
        $this->exceptionHandler = $this->prophesize(ExceptionHandler::class);

        $this->endpoint = new JsonRpcEndpoint(
            $this->jsonRpcCallSerializer->reveal(),
            $this->jsonRpcRequestHandler->reveal(),
            $this->exceptionHandler->reveal()
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
        /** @var ObjectProphecy|JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = $this->prophesize(JsonRpcCall::class);

        $jsonRpcCall->isBatch()->willReturn(false)->shouldBeCalled();

        $this->jsonRpcCallSerializer->deserialize($requestString)
            ->willReturn($jsonRpcCall->reveal())
            ->shouldBeCalled();

        $jsonRpcCall->getItemList()
            ->willReturn([$fakeRequestItem->reveal()])
            ->shouldBeCalled();

        $this->jsonRpcRequestHandler->processJsonRpcRequest($fakeRequestItem->reveal())
            ->willThrow($fakeHandleException->reveal())
            ->shouldBeCalled();

        $this->exceptionHandler->getJsonRpcResponseFromException(
            $fakeHandleException->reveal(),
            $fakeRequestItem->reveal()
        )
            ->willReturn($fakeResponseItem->reveal())
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::allOf(
            Argument::type(JsonRpcCallResponse::class),
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
