<?php
namespace Tests\Functional\App\Handler;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\Domain\Event\Action\OnExceptionEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Handler\ExceptionHandler
 *
 * @group ExceptionHandler
 */
class ExceptionHandlerWithDispatcherTest extends TestCase
{
    /** @var ExceptionHandler */
    private $exceptionHandler;
    /** @var ResponseCreator */
    private $responseCreator;
    /** @var JsonRpcServerDispatcherInterface|ObjectProphecy */
    private $jsonRpcServerDispatcher;

    public function setUp()
    {
        $this->responseCreator = $this->prophesize(ResponseCreator::class);
        $this->jsonRpcServerDispatcher = $this->prophesize(JsonRpcServerDispatcherInterface::class);

        $this->exceptionHandler = new ExceptionHandler(
            $this->responseCreator->reveal()
        );

        $this->exceptionHandler->setJsonRpcServerDispatcher($this->jsonRpcServerDispatcher->reveal());
    }

    public function testShouldDispatchEventBeforeCreatingResponseAndUseExceptionFromEvent()
    {
        /** @var ObjectProphecy|\Exception $fakeException */
        $fakeException = $this->prophesize(\Exception::class);

        $this->responseCreator->createErrorResponse(Argument::cetera())
            ->willReturn($this->prophesize(JsonRpcResponse::class)->reveal())
            ->shouldBeCalled();

        $this->exceptionHandler->getJsonRpcResponseFromException($fakeException->reveal());

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_exception',
            Argument::allOf(
                Argument::type(OnExceptionEvent::class),
                Argument::which('getFromJsonRpcRequest', null),
                Argument::which('getException', $fakeException->reveal())
            )
        )->shouldHaveBeenCalled();
    }

    public function testShouldDispatchEventWithRequestBeforeCreatingResponseAndUseExceptionFromEvent()
    {
        /** @var ObjectProphecy|\Exception $fakeException */
        $fakeException = $this->prophesize(\Exception::class);
        /** @var ObjectProphecy|JsonRpcRequest $fakeRequest */
        $fakeRequest = $this->prophesize(JsonRpcRequest::class);

        $this->responseCreator->createErrorResponse(Argument::cetera())
            ->willReturn($this->prophesize(JsonRpcResponse::class)->reveal())
            ->shouldBeCalled();

        $this->exceptionHandler->getJsonRpcResponseFromException($fakeException->reveal(), $fakeRequest->reveal());

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            'json_rpc_server_skd.on_exception',
            Argument::allOf(
                Argument::type(OnExceptionEvent::class),
                Argument::which('getFromJsonRpcRequest', $fakeRequest->reveal()),
                Argument::which('getException', $fakeException->reveal())
            )
        )->shouldHaveBeenCalled();
    }
}
