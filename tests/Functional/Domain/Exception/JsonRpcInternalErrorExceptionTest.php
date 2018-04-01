<?php
namespace Tests\Technical\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException
 */
class JsonRpcInternalErrorExceptionTest extends TestCase
{
    public function testShouldHaveTheRightJsonRpcErrorCode()
    {
        $exception = new JsonRpcInternalErrorException();

        $this->assertSame(-32603, $exception->getErrorCode());
    }

    public function testShouldHandleAnExceptionAnPutItInExceptionData()
    {
        $previousException = new \Exception('my-exception');

        $exception = new JsonRpcInternalErrorException($previousException);

        $this->assertArrayHasKey(
            JsonRpcInternalErrorException::DATA_PREVIOUS_KEY,
            $exception->getErrorData()
        );
        $this->assertSame(
            $previousException,
            $exception->getErrorData()[JsonRpcInternalErrorException::DATA_PREVIOUS_KEY]
        );
    }
}
