<?php
namespace Tests\Functional\Infra\Endpoint;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
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

    public function testShouldHandleARequest()
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem */
        $fakeRequestItem = $this->prophesize(JsonRpcRequest::class);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeResponseItem = $this->prophesize(JsonRpcResponse::class);
        /** @var JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = (new JsonRpcCall(false))
            ->addRequestItem($fakeRequestItem->reveal())
        ;

        $this->jsonRpcCallSerializer->deserialize($requestString)
            ->willReturn($jsonRpcCall)
            ->shouldBeCalled();

        $this->jsonRpcRequestHandler->processJsonRpcRequest($fakeRequestItem)
            ->willReturn($fakeResponseItem->reveal())
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::allOf(
            Argument::type(JsonRpcCallResponse::class),
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
        /** @var JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = (new JsonRpcCall(true))
            ->addRequestItem($fakeRequestItem->reveal())
            ->addRequestItem($fakeRequestItem2->reveal())
        ;

        $this->jsonRpcCallSerializer->deserialize($requestString)
            ->willReturn($jsonRpcCall)
            ->shouldBeCalled();

        $this->jsonRpcRequestHandler->processJsonRpcRequest($fakeRequestItem->reveal())
            ->willReturn($fakeResponseItem)
            ->shouldBeCalled();
        $this->jsonRpcRequestHandler->processJsonRpcRequest($fakeRequestItem2->reveal())
            ->willReturn($fakeResponseItem2)
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::allOf(
            Argument::type(JsonRpcCallResponse::class),
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
        /** @var JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = (new JsonRpcCall(false))
            ->addExceptionItem($fakeException->reveal());

        $this->jsonRpcCallSerializer->deserialize($requestString)
            ->willReturn($jsonRpcCall)
            ->shouldBeCalled();

        $this->exceptionHandler->getJsonRpcResponseFromException($fakeException->reveal(), null)
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
        /** @var JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = (new JsonRpcCall(true))
            ->addRequestItem($fakeRequestItem->reveal())
            ->addExceptionItem($fakeExceptionItem->reveal())
        ;


        $this->jsonRpcCallSerializer->deserialize($requestString)
            ->willReturn($jsonRpcCall)
            ->shouldBeCalled();

        $this->jsonRpcRequestHandler->processJsonRpcRequest($fakeRequestItem->reveal())
            ->willReturn($fakeResponseItem)
            ->shouldBeCalled();
        $this->exceptionHandler->getJsonRpcResponseFromException($fakeExceptionItem->reveal(), null)
            ->willReturn($fakeResponseItem2)
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::allOf(
            Argument::type(JsonRpcCallResponse::class),
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
