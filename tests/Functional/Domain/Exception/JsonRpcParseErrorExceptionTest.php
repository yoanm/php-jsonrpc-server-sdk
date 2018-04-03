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

    public function testShouldHandleAContent()
    {
        $content = 'my-content';

        $exception = new JsonRpcParseErrorException($content);

        $this->assertSame(
            $content,
            $exception->getContent()
        );
    }

    public function testShouldHandleAParseErrorCode()
    {
        $code = 'my-error-code';

        $exception = new JsonRpcParseErrorException(self::DEFAULT_CONTENT, $code);
        
        $this->assertSame($code, $exception->getParseErrorCode());
    }

    public function testShouldHandleAParseErrorMessage()
    {
        $message = 'my-message';

        $exception = new JsonRpcParseErrorException(self::DEFAULT_CONTENT, 1234, $message);

        $this->assertSame($message, $exception->getParseErrorMessage());
    }
}
