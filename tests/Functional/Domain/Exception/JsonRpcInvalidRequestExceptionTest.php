<?php
namespace Tests\Technical\Domain\Model\Exception;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException
 *
 * @group Exceptions
 */
class JsonRpcInvalidRequestExceptionTest extends TestCase
{
    public function testShouldHaveTheRightJsonRpcErrorCode()
    {
        $exception = new JsonRpcInvalidRequestException('my-content');

        $this->assertSame(-32600, $exception->getErrorCode());
    }

    public function testShouldHandleAContent()
    {
        $content = 'my-content';

        $exception = new JsonRpcInvalidRequestException($content);

        $this->assertSame($content, $exception->getContent());
    }

    public function testShouldHandleAnOptionalDescription()
    {
        $description = 'my-description';

        $exception = new JsonRpcInvalidRequestException('a-content', $description);

        $this->assertSame($description, $exception->getDescription());
    }
}
