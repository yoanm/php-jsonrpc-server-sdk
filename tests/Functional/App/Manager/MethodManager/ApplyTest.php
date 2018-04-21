<?php
namespace Tests\Functional\App\Manager\MethodManager;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidParamsException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;

/**
 * @covers \Yoanm\JsonRpcServer\App\Manager\MethodManager
 */
class ApplyTest extends TestCase
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
     * Should resolve return the value returned by the resolved method
     */
    public function testShouldReturnMethodReturnedValue()
    {
        $expectedReturn = 'expected-return';
        $methodName = 'methodName';
        $paramList = ['param-list'];

        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $this->methodResolver->resolve($methodName)
            ->willReturn($method->reveal())
            ->shouldBeCalled();

        $method->apply($paramList)
            ->willReturn($expectedReturn)
            ->shouldBeCalled();

        $method->validateParams($paramList)
            ->shouldBeCalled();

        $this->assertSame(
            $expectedReturn,
            $this->methodManager->apply($methodName, $paramList)
        );
    }

    /**
     * Should handle params violation list returned by the method validator
     * and throw proper JSON-RPC exception
     */
    public function testShouldHandleParamsViolationList()
    {
        $methodName = 'methodName';
        $paramList = ['param-list'];
        $validationErrorMessage = 'validation-error-message';

        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $this->methodResolver->resolve($methodName)
            ->willReturn($method->reveal())
            ->shouldBeCalled();

        $method->validateParams($paramList)
            ->willReturn([$validationErrorMessage])
            ->shouldBeCalled();

        $this->expectException(JsonRpcInvalidParamsException::class);

        $this->methodManager->apply($methodName, $paramList);
    }

    /**
     * Should handle a the case where method does not exist
     * and throw proper JSON-RPC exception
     */
    public function testShouldHandleMethodThatDoesNotExist()
    {
        $methodName = 'methodName';

        $this->methodResolver->resolve($methodName)
            ->willReturn(null)
            ->shouldBeCalled();

        $this->expectException(JsonRpcMethodNotFoundException::class);

        $this->methodManager->apply($methodName);
    }
}
