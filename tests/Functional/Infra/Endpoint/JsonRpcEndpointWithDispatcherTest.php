<?php
namespace Tests\Functional\Infra\Endpoint;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnBatchSubRequestProcessedEvent;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnBatchSubRequestProcessingEvent;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnRequestReceivedEvent;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnResponseSendingEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint
 *
 * @group JsonRpcEndpoint
 */
class JsonRpcEndpointWithDispatcherTest extends TestCase
{
    /** @var JsonRpcEndpoint */
    private $endpoint;
    /** @var JsonRpcCallSerializer|ObjectProphecy */
    private $jsonRpcCallSerializer;
    /** @var JsonRpcRequestHandler|ObjectProphecy */
    private $jsonRpcRequestHandler;
    /** @var ExceptionHandler|ObjectProphecy */
    private $exceptionHandler;
    /** @var JsonRpcServerDispatcherInterface|ObjectProphecy */
    private $jsonRpcServerDispatcher;

    protected function setUp(): void
    {
        $this->jsonRpcCallSerializer = $this->prophesize(JsonRpcCallSerializer::class);
        $this->jsonRpcRequestHandler = $this->prophesize(JsonRpcRequestHandler::class);
        $this->exceptionHandler = $this->prophesize(ExceptionHandler::class);
        $this->jsonRpcServerDispatcher = $this->prophesize(JsonRpcServerDispatcherInterface::class);

        $this->endpoint = new JsonRpcEndpoint(
            $this->jsonRpcCallSerializer->reveal(),
            $this->jsonRpcRequestHandler->reveal(),
            $this->exceptionHandler->reveal()
        );

        $this->endpoint->setJsonRpcServerDispatcher($this->jsonRpcServerDispatcher->reveal());
    }

    public function testShouldDispatchEventsForNormalRequest()
    {
        $requestString = 'request-string';
        $responseString = 'expected-response-string';
        /** @var JsonRpcRequest|ObjectProphecy $request */
        $request = $this->prophesize(JsonRpcRequest::class);
        /** @var JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = (new JsonRpcCall(false))
            ->addRequestItem($request->reveal())
        ;

        $this->jsonRpcCallSerializer->deserialize(Argument::cetera())
            ->willReturn($jsonRpcCall)
            ->shouldBeCalled();

        $this->jsonRpcRequestHandler->processJsonRpcRequest(Argument::cetera())
            ->willReturn($this->prophesize(JsonRpcResponse::class)->reveal())
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::cetera())
            ->willReturn($responseString)
            ->shouldBeCalled();

        $this->endpoint->index($requestString);

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_request_received',
            Argument::allOf(
                Argument::type(OnRequestReceivedEvent::class),
                Argument::which('getRequest', $requestString),
                Argument::which('getJsonRpcCall', $jsonRpcCall)
            )
        )->shouldHaveBeenCalled();

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_response_sending',
            Argument::allOf(
                Argument::type(OnResponseSendingEvent::class),
                Argument::which('getResponseString', $responseString),
                Argument::which('getJsonRpcCall', $jsonRpcCall)
            )
        )->shouldHaveBeenCalled();
    }

    public function testShouldDispatchEventsForBatchRequest()
    {
        $requestString = 'request-string';
        $responseString = 'expected-response-string';

        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem */
        $fakeRequestItem = $this->prophesize(JsonRpcRequest::class);
        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem2 */
        $fakeRequestItem2 = $this->prophesize(JsonRpcRequest::class);
        /** @var ObjectProphecy|JsonRpcResponse $request */
        $request = $this->prophesize(JsonRpcResponse::class);
        /** @var JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = (new JsonRpcCall(true))
            ->addRequestItem($fakeRequestItem->reveal())
            ->addRequestItem($fakeRequestItem2->reveal())
        ;

        $this->jsonRpcCallSerializer->deserialize(Argument::cetera())
            ->willReturn($jsonRpcCall)
            ->shouldBeCalled();

        $this->jsonRpcRequestHandler->processJsonRpcRequest(Argument::cetera())
            ->willReturn($request->reveal())
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::cetera())
            ->willReturn($responseString)
            ->shouldBeCalled();

        $this->endpoint->index($requestString);

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_request_received',
            Argument::allOf(
                Argument::type(OnRequestReceivedEvent::class),
                Argument::which('getRequest', $requestString),
                Argument::which('getJsonRpcCall', $jsonRpcCall)
            )
        )->shouldHaveBeenCalled();

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_batch_sub_request_processing',
            Argument::allOf(
                Argument::type(OnBatchSubRequestProcessingEvent::class),
                Argument::which('getItemPosition', 0)
            )
        )->shouldHaveBeenCalled();
        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_batch_sub_request_processed',
            Argument::allOf(
                Argument::type(OnBatchSubRequestProcessedEvent::class),
                Argument::which('getItemPosition', 0)
            )
        )->shouldHaveBeenCalled();

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_batch_sub_request_processing',
            Argument::allOf(
                Argument::type(OnBatchSubRequestProcessingEvent::class),
                Argument::which('getItemPosition', 1)
            )
        )->shouldHaveBeenCalled();
        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_batch_sub_request_processed',
            Argument::allOf(
                Argument::type(OnBatchSubRequestProcessedEvent::class),
                Argument::which('getItemPosition', 1)
            )
        )->shouldHaveBeenCalled();

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_response_sending',
            Argument::allOf(
                Argument::type(OnResponseSendingEvent::class),
                Argument::which('getResponseString', $responseString),
                Argument::which('getJsonRpcCall', $jsonRpcCall)
            )
        )->shouldHaveBeenCalled();
    }
}
