<?php
namespace Tests\Functional\Infra\Endpoint;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\RequestHandler;
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

    public function testShouldHandleARequest()
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem */
        $fakeRequestItem = $this->prophesize(JsonRpcRequest::class);
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
            ->willReturn($fakeResponseItem->reveal())
            ->shouldBeCalled();

        $this->rawResponseNormalizer->serialize(Argument::allOf(
            Argument::type(JsonRpcRawResponse::class),
            Argument::which('isBatch', false),
            Argument::which('getResponseList', [$fakeResponseItem->reveal()])
        ))
            ->willReturn($expectedResponseString)
            ->shouldBeCalled();

        $this->assertSame(
            $expectedResponseString,
            $this->endpoint->index($requestString)
        );
    }

    public function testShouldHandleABatchRequest()
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem */
        $fakeRequestItem = $this->prophesize(JsonRpcRequest::class);
        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem2 */
        $fakeRequestItem2 = $this->prophesize(JsonRpcRequest::class);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeResponseItem = $this->prophesize(JsonRpcResponse::class);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem2 */
        $fakeResponseItem2 = $this->prophesize(JsonRpcResponse::class);
        /** @var ObjectProphecy|JsonRpcRawRequest $jsonRpcRawRequest */
        $jsonRpcRawRequest = $this->prophesize(JsonRpcRawRequest::class);

        $jsonRpcRawRequest->isBatch()->willReturn(true)->shouldBeCalled();

        $this->rawRequestSerializer->deserialize($requestString)
            ->willReturn($jsonRpcRawRequest->reveal())
            ->shouldBeCalled();

        $jsonRpcRawRequest->getItemtList()
            ->willReturn([$fakeRequestItem->reveal(), $fakeRequestItem2->reveal()])
            ->shouldBeCalled();

        $this->requestHandler->handle($fakeRequestItem->reveal())
            ->willReturn($fakeResponseItem)
            ->shouldBeCalled();
        $this->requestHandler->handle($fakeRequestItem2->reveal())
            ->willReturn($fakeResponseItem2)
            ->shouldBeCalled();

        $this->rawResponseNormalizer->serialize(Argument::allOf(
            Argument::type(JsonRpcRawResponse::class),
            Argument::which('isBatch', true),
            Argument::which('getResponseList', [$fakeResponseItem->reveal(), $fakeResponseItem2->reveal()])
        ))
            ->willReturn($expectedResponseString)
            ->shouldBeCalled();

        $this->assertSame(
            $expectedResponseString,
            $this->endpoint->index($requestString)
        );
    }

    public function testShouldHandleRequestOnError()
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        /** @var ObjectProphecy|\Exception $fakeException */
        $fakeException = $this->prophesize(\Exception::class);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeResponseItem = $this->prophesize(JsonRpcResponse::class);
        /** @var ObjectProphecy|JsonRpcRawRequest $jsonRpcRawRequest */
        $jsonRpcRawRequest = $this->prophesize(JsonRpcRawRequest::class);

        $jsonRpcRawRequest->isBatch()->willReturn(false)->shouldBeCalled();

        $this->rawRequestSerializer->deserialize($requestString)
            ->willReturn($jsonRpcRawRequest->reveal())
            ->shouldBeCalled();

        $jsonRpcRawRequest->getItemtList()
            ->willReturn([$fakeException->reveal()])
            ->shouldBeCalled();

        $this->responseCreator->createErrorResponse($fakeException)
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

    public function testShouldHandleRequestOnErrorInsideBatchRequest()
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem */
        $fakeRequestItem = $this->prophesize(JsonRpcRequest::class);
        /** @var ObjectProphecy|\Exception $fakeExceptionItem */
        $fakeExceptionItem = $this->prophesize(\Exception::class);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeResponseItem = $this->prophesize(JsonRpcResponse::class);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem2 */
        $fakeResponseItem2 = $this->prophesize(JsonRpcResponse::class);
        /** @var ObjectProphecy|JsonRpcRawRequest $jsonRpcRawRequest */
        $jsonRpcRawRequest = $this->prophesize(JsonRpcRawRequest::class);

        $jsonRpcRawRequest->isBatch()->willReturn(true)->shouldBeCalled();

        $this->rawRequestSerializer->deserialize($requestString)
            ->willReturn($jsonRpcRawRequest->reveal())
            ->shouldBeCalled();

        $jsonRpcRawRequest->getItemtList()
            ->willReturn([$fakeRequestItem->reveal(), $fakeExceptionItem->reveal()])
            ->shouldBeCalled();

        $this->requestHandler->handle($fakeRequestItem->reveal())
            ->willReturn($fakeResponseItem)
            ->shouldBeCalled();
        $this->responseCreator->createErrorResponse($fakeExceptionItem->reveal())
            ->willReturn($fakeResponseItem2)
            ->shouldBeCalled();

        $this->rawResponseNormalizer->serialize(Argument::allOf(
            Argument::type(JsonRpcRawResponse::class),
            Argument::which('isBatch', true),
            Argument::which('getResponseList', [$fakeResponseItem->reveal(), $fakeResponseItem2->reveal()])
        ))
            ->willReturn($expectedResponseString)
            ->shouldBeCalled();

        $this->assertSame(
            $expectedResponseString,
            $this->endpoint->index($requestString)
        );
    }
}
