<?php
namespace Tests\Functional\Infra\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Infra\Resolver\ArrayMethodResolver;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Resolver\ArrayMethodResolver
 */
class ArrayMethodResolverTest extends TestCase
{
    const FIRST_DEFAULT_METHOD_NAME = 'a-method';
    const SECOND_DEFAULT_METHOD_NAME = 'getDummy';
    const THIRD_DEFAULT_METHOD_NAME = 'method-1';

    /** @var ArrayMethodResolver */
    private $arrayMethodResolver;

    protected function setUp()
    {
        $this->arrayMethodResolver = new ArrayMethodResolver();
    }

    public function testShouldReturnTheGivenMethod()
    {
        $methodMappingList = $this->initResolver();

        $this->assertValidMethodMapping($methodMappingList, self::SECOND_DEFAULT_METHOD_NAME);
        $this->assertValidMethodMapping($methodMappingList, self::FIRST_DEFAULT_METHOD_NAME);
        $this->assertValidMethodMapping($methodMappingList, self::THIRD_DEFAULT_METHOD_NAME);
    }

    public function testShouldReturnNullIfMethodDoesNotExist()
    {
        $this->initResolver();

        $this->assertNull(
            $this->arrayMethodResolver->resolve('a-non-existent-method')
        );
    }

    /**
     * @param ObjectProphecy[] $methodMappingList
     * @param string           $methodName
     */
    private function assertValidMethodMapping(array $methodMappingList, string $methodName)
    {
        $this->assertSame($methodMappingList[$methodName]->reveal(), $this->arrayMethodResolver->resolve($methodName));
    }

    /**
     * @return array
     */
    private function initResolver()
    {
        $methodMappingList = [
            self::FIRST_DEFAULT_METHOD_NAME => $this->prophesize(JsonRpcMethodInterface::class),
            self::SECOND_DEFAULT_METHOD_NAME => $this->prophesize(JsonRpcMethodInterface::class),
            self::THIRD_DEFAULT_METHOD_NAME => $this->prophesize(JsonRpcMethodInterface::class),
        ];

        foreach ($methodMappingList as $methodName => $method) {
            $this->arrayMethodResolver->addMethod($method->reveal(), $methodName);
        }

        return $methodMappingList;
    }
}
