<?php
namespace Tests\Functional\App\Handler;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\Domain\Event\Action\ValidateParamsEvent;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidParamsException;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler
 *
 * @group JsonRpcRequestHandler
 */
class JsonRpcRequestHandlerWithDispatcherTest extends TestCase
{
    /** @var JsonRpcRequestHandler */
    private $requestHandler;
    /** @var JsonRpcMethodResolverInterface|ObjectProphecy */
    private $methodResolver;
    /** @var ResponseCreator|ObjectProphecy */
    private $responseCreator;
    /** @var JsonRpcServerDispatcherInterface|ObjectProphecy */
    private $jsonRpcServerDispatcher;

    protected function setUp()
    {
        $this->methodResolver = $this->prophesize(JsonRpcMethodResolverInterface::class);
        $this->responseCreator = $this->prophesize(ResponseCreator::class);
        $this->jsonRpcServerDispatcher = $this->prophesize(JsonRpcServerDispatcherInterface::class);

        $this->requestHandler = new JsonRpcRequestHandler(
            $this->methodResolver->reveal(),
            $this->responseCreator->reveal()
        );

        $this->requestHandler->setJsonRpcServerDispatcher($this->jsonRpcServerDispatcher->reveal());
    }

    public function testShouldThrowAnExceptionIfParamsValidationFail()
    {
        $request = new JsonRpcRequest('json-rpc-version', 'method');
        $myViolation = ['violation'];
        /** @var JsonRpcResponse|ObjectProphecy $response */
        $response = $this->prophesize(JsonRpcResponse::class);

        $this->methodResolver->resolve(Argument::cetera())
            ->willReturn($this->prophesize(JsonRpcMethodInterface::class)->reveal())
            ->shouldBeCalled()
        ;

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent(
            ValidateParamsEvent::EVENT_NAME,
            Argument::type(ValidateParamsEvent::class)
        )
            ->will(function ($args) use ($myViolation) {
                /** @var ValidateParamsEvent $event */
                $event = $args[1];
                $event->addViolation($myViolation);
            })
            ->shouldBeCalled()
        ;

        $this->expectException(JsonRpcInvalidParamsException::class);

        try {
            $this->requestHandler->processJsonRpcRequest($request);
        } catch (JsonRpcInvalidParamsException $exception) {
            $this->assertSame($exception->getErrorData(), ['violations' => [$myViolation]]);

            throw $exception;
        }
    }
}
