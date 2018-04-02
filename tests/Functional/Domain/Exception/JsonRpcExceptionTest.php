<?php
namespace Tests\Technical\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException
 */
class JsonRpcExceptionTest extends TestCase
{
    public function testShouldHandleACode()
    {
        $code = 4321;

        $this->assertSame($code, (new JsonRpcException($code))->getErrorCode());
    }

    public function testShouldHandleAnOptionalMessage()
    {
        $message = 'my-message';
        $this->assertSame(
            $message,
            (new JsonRpcException(4321, $message))->getErrorMessage()
        );
    }

    public function testShouldHandleAnOptionalData()
    {
        $data = ['my-data'];
        $this->assertSame(
            $data,
            (new JsonRpcException(4321, '', $data))->getErrorData()
        );
    }
}
