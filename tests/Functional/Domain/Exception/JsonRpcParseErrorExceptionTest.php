<?php
namespace Tests\Functional\Domain\Exception;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcParseErrorException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcParseErrorException
 *
 * @group Exceptions
 */
class JsonRpcParseErrorExceptionTest extends TestCase
{
    use ProphecyTrait;

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
