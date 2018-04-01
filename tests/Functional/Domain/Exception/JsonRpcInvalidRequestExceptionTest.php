<?php
namespace Tests\Technical\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException
 */
class JsonRpcInvalidRequestExceptionTest extends TestCase
{
    public function testShouldHandleAContentAnPutItInExceptionData()
    {
        $content = 'my-content';

        $exception = new JsonRpcInvalidRequestException($content);

        $this->assertArrayHasKey(
            JsonRpcInvalidRequestException::CONTENT_KEY,
            $exception->getErrorData()
        );
        $this->assertSame(
            $content,
            $exception->getErrorData()[JsonRpcInvalidRequestException::CONTENT_KEY]
        );
    }

    public function testShouldHandleAnOptionalDescriptionAnPutItInExceptionData()
    {
        $description = 'my-description';

        $exception = new JsonRpcInvalidRequestException('a-content', $description);

        $this->assertArrayHasKey(
            JsonRpcInvalidRequestException::DESCRIPTION_KEY,
            $exception->getErrorData()
        );
        $this->assertSame(
            $description,
            $exception->getErrorData()[JsonRpcInvalidRequestException::DESCRIPTION_KEY]
        );
    }
}
