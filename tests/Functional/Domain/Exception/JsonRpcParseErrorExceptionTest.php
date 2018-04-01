<?php
namespace Tests\Technical\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcParseErrorException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcParseErrorException
 */
class JsonRpcParseErrorExceptionTest extends TestCase
{
    const DEFAULT_CONTENT = 'default-content';

    public function testShouldHaveTheRightJsonRpcErrorCode()
    {
        $exception = new JsonRpcParseErrorException(self::DEFAULT_CONTENT);

        $this->assertSame(-32700, $exception->getErrorCode());
    }

    public function testShouldHandleAContentAnPutItInExceptionData()
    {
        $content = 'my-content';

        $exception = new JsonRpcParseErrorException($content);

        $this->assertArrayHasKey(
            JsonRpcParseErrorException::DATA_CONTENT_KEY,
            $exception->getErrorData()
        );
        $this->assertSame(
            $content,
            $exception->getErrorData()[JsonRpcParseErrorException::DATA_CONTENT_KEY]
        );
    }

    public function testShouldHandleAParseErrorCodeAnPutItInExceptionData()
    {
        $code = 'my-error-code';

        $exception = new JsonRpcParseErrorException(self::DEFAULT_CONTENT, $code);

        $this->assertArrayHasKey(JsonRpcParseErrorException::DATA_ERROR_KEY, $exception->getErrorData());

        $errorData = $exception->getErrorData()[JsonRpcParseErrorException::DATA_ERROR_KEY];
        $this->assertArrayHasKey(JsonRpcParseErrorException::DATA_ERROR_CODE_KEY, $errorData);

        $this->assertSame($code, $errorData[JsonRpcParseErrorException::DATA_ERROR_CODE_KEY]);
    }

    public function testShouldHandleAParseErrorMessageAnPutItInExceptionData()
    {
        $message = 'my-message';

        $exception = new JsonRpcParseErrorException(self::DEFAULT_CONTENT, 1234, $message);

        $this->assertArrayHasKey(JsonRpcParseErrorException::DATA_ERROR_KEY, $exception->getErrorData());

        $errorData = $exception->getErrorData()[JsonRpcParseErrorException::DATA_ERROR_KEY];
        $this->assertArrayHasKey(JsonRpcParseErrorException::DATA_ERROR_MESSAGE_KEY, $errorData);

        $this->assertSame($message, $errorData[JsonRpcParseErrorException::DATA_ERROR_MESSAGE_KEY]);
    }
}
