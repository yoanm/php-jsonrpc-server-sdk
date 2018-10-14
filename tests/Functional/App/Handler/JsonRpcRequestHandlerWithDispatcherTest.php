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
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodParamsValidatorInterface;
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
        $violationList = [$myViolation];

        /** @var JsonRpcMethodParamsValidatorInterface $methodParamsValidator */
        $methodParamsValidator = $this->prophesize(JsonRpcMethodParamsValidatorInterface::class);
        /** @var JsonRpcMethodInterface $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $this->methodResolver->resolve(Argument::cetera())
            ->willReturn($method->reveal())
            ->shouldBeCalled()
        ;

        $methodParamsValidator->validate($request, $method)
            ->willReturn($violationList)
            ->shouldBeCalled()
        ;

        $this->requestHandler->setMethodParamsValidator($methodParamsValidator->reveal());

        $this->expectException(JsonRpcInvalidParamsException::class);

        try {
            $this->requestHandler->processJsonRpcRequest($request);
        } catch (JsonRpcInvalidParamsException $exception) {
            $this->assertSame($exception->getErrorData(), ['violations' => [$myViolation]]);

            throw $exception;
        }
    }
}
