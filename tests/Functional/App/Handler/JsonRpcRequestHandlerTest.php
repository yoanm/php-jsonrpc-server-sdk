<?php
namespace Tests\Functional\App\Handler;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler
 *
 * @group JsonRpcRequestHandler
 */
class JsonRpcRequestHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var JsonRpcRequestHandler */
    private $requestHandler;
    /** @var JsonRpcMethodResolverInterface|ObjectProphecy */
    private $methodResolver;
    /** @var ResponseCreator|ObjectProphecy */
    private $responseCreator;

    protected function setUp(): void
    {
        $this->methodResolver = $this->prophesize(JsonRpcMethodResolverInterface::class);
        $this->responseCreator = $this->prophesize(ResponseCreator::class);

        $this->requestHandler = new JsonRpcRequestHandler(
            $this->methodResolver->reveal(),
            $this->responseCreator->reveal()
        );
    }

    public function testShouldReturnAResponse()
    {
        $request = new JsonRpcRequest('json-rpc-version', 'method');
        $response = $this->prophesize(JsonRpcResponse::class);

        $this->methodResolver->resolve(Argument::cetera())
            ->willReturn($this->prophesize(JsonRpcMethodInterface::class))
            ->shouldBeCalled()
        ;
        $this->responseCreator->createResultResponse(Argument::cetera())
            ->willReturn($response->reveal())
            ->shouldBeCalled()
        ;

        $this->assertSame(
            $response->reveal(),
            $this->requestHandler->processJsonRpcRequest($request)
        );
    }

    public function testShouldHandleMethodExecutionException()
    {
        $request = new JsonRpcRequest('json-rpc-version', 'method');
        $exception = new \Exception('my-message');
        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);
        /** @var JsonRpcResponse|ObjectProphecy $response */
        $response = $this->prophesize(JsonRpcResponse::class);

        $this->methodResolver->resolve(Argument::cetera())
            ->willReturn($method->reveal())
            ->shouldBeCalled()
        ;
        $this->responseCreator->createErrorResponse($exception, $request)
            ->willReturn($response->reveal())
            ->shouldBeCalled()
        ;

        $method->apply(Argument::cetera())
            ->willThrow($exception)
            ->shouldBeCalled()
        ;

        $this->assertSame(
            $response->reveal(),
            $this->requestHandler->processJsonRpcRequest($request)
        );
    }

    public function testShouldThrowAnExceptionIfMethodCannotBeResolved()
    {
        $methodName = 'method';
        $request = new JsonRpcRequest('json-rpc-version', $methodName);

        $this->methodResolver->resolve(Argument::cetera())
            ->willReturn(null)
            ->shouldBeCalled()
        ;

        $this->expectException(JsonRpcMethodNotFoundException::class);

        try {
            $this->requestHandler->processJsonRpcRequest($request);
        } catch (JsonRpcMethodNotFoundException $exception) {
            $this->assertSame($methodName, $exception->getMethodName());

            throw $exception;
        }
    }
}
