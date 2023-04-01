<?php
namespace Tests\Functional\Domain\Exception;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException
 *
 * @group Exceptions
 */
class JsonRpcInternalErrorExceptionTest extends TestCase
{
    use ProphecyTrait;

    public function testShouldHaveTheRightJsonRpcErrorCode()
    {
        $exception = new JsonRpcInternalErrorException();

        $this->assertSame(-32603, $exception->getErrorCode());
    }

    public function testShouldHandleAnExceptionAnPutItInExceptionData()
    {
        $message = 'my-exception';
        $previousException = new \Exception($message);

        $exception = new JsonRpcInternalErrorException($previousException);

        $this->assertEmpty($exception->getErrorData());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
