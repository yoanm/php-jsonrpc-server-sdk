<?php
namespace Tests\Technical\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidParamsException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidParamsException
 */
class JsonRpcInvalidParamsExceptionTest extends TestCase
{
    const DEFAULT_MESSAGE = 'default-message';
    const DEFAULT_PATH = 'my-path';

    public function testShouldHaveTheRightJsonRpcErrorCode()
    {
        $exception = new JsonRpcInvalidParamsException([self::DEFAULT_MESSAGE]);

        $this->assertSame(-32602, $exception->getErrorCode());
    }

    public function testShouldHandleAMessageAnPutItInExceptionData()
    {
        $violationList = [
            'message' => self::DEFAULT_MESSAGE,
            [
                'path' => self::DEFAULT_PATH,
                'message' => self::DEFAULT_MESSAGE.'_2'
            ]
        ];

        $exception = new JsonRpcInvalidParamsException($violationList);

        $this->assertArrayHasKey(
            JsonRpcInvalidParamsException::DATA_VIOLATIONS_KEY,
            $exception->getErrorData()
        );
        $this->assertSame(
            $violationList,
            $exception->getErrorData()[JsonRpcInvalidParamsException::DATA_VIOLATIONS_KEY]
        );
    }
}
