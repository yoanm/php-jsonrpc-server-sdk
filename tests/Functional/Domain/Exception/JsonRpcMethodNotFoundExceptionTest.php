<?php
namespace Tests\Technical\Domain\Exception;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException
 *
 * @group Exceptions
 */
class JsonRpcMethodNotFoundExceptionTest extends TestCase
{
    const DEFAULT_METHOD = 'default-method';

    public function testShouldHaveTheRightJsonRpcErrorCode()
    {
        $exception = new JsonRpcMethodNotFoundException(self::DEFAULT_METHOD);

        $this->assertSame(-32601, $exception->getErrorCode());
    }

    public function testShouldHandleAMethod()
    {
        $method = 'my-method';

        $exception = new JsonRpcMethodNotFoundException($method);

        $this->assertSame(
            $method,
            $exception->getMethodName()
        );
    }
}
