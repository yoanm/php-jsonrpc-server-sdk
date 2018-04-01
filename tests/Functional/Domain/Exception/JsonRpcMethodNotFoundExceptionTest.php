<?php
namespace Tests\Technical\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException
 */
class JsonRpcMethodNotFoundExceptionTest extends TestCase
{
    const DEFAULT_METHOD = 'default-method';

    public function testShouldHaveTheRightJsonRpcErrorCode()
    {
        $exception = new JsonRpcMethodNotFoundException(self::DEFAULT_METHOD);

        $this->assertSame(-32601, $exception->getErrorCode());
    }

    public function testShouldHandleAMethodAnPutItInExceptionData()
    {
        $method = 'my-method';

        $exception = new JsonRpcMethodNotFoundException($method);

        $this->assertArrayHasKey(
            JsonRpcMethodNotFoundException::DATA_METHOD_KEY,
            $exception->getErrorData()
        );
        $this->assertSame(
            $method,
            $exception->getErrorData()[JsonRpcMethodNotFoundException::DATA_METHOD_KEY]
        );
    }
}
