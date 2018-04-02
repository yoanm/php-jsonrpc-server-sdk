<?php
namespace Tests\Technical\App\Manager;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidParamsException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;

/**
 * @covers \Yoanm\JsonRpcServer\App\Manager\MethodManager
 */
class MethodManagerTest extends TestCase
{
    /** @var MethodManager */
    private $methodManager;

    /** @var MethodResolverInterface|ObjectProphecy */
    private $methodResolver;
    /** @var CustomExceptionCreator|ObjectProphecy */
    private $customExceptionCreator;

    protected function setUp()
    {
        $this->methodResolver = $this->prophesize(MethodResolverInterface::class);
        $this->customExceptionCreator = $this->prophesize(CustomExceptionCreator::class);

        $this->methodManager = new MethodManager(
            $this->methodResolver->reveal(),
            $this->customExceptionCreator->reveal()
        );
    }

    /**
     * Should handle an exception during method execution
     * and throw proper JSON-RPC exception
     */
    public function testApplyShouldHandleMethodExecutionException()
    {
        $methodName = 'methodName';
        $paramList = ['param-list'];
        $executionErrorMessage = 'execution-error-message';
        $executionException = new \Exception($executionErrorMessage);
        $expectedException = $this->prophesize(JsonRpcExceptionInterface::class)
            ->willExtend(\Exception::class);

        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $this->methodResolver->resolve($methodName)
            ->willReturn($method->reveal())
            ->shouldBeCalled();

        $method->apply($paramList)
            ->willThrow($executionException)
            ->shouldBeCalled();


        $this->customExceptionCreator->createFor($executionException)
            ->willReturn($expectedException->reveal())
            ->shouldBeCalled();

        $method->validateParams($paramList)
            ->shouldBeCalled();

        $this->expectException(JsonRpcExceptionInterface::class);

        try {
            $this->methodManager->apply($methodName, $paramList);
        } catch (JsonRpcException $e) {
            $this->assertSame($expectedException->reveal(), $e);

            throw $e;
        }
    }
}
