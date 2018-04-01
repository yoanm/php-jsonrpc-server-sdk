<?php
namespace Tests\Technical\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse
 */
class JsonRpcResponseTest extends TestCase
{
    use IdProviderTrait;

    /**
     * @dataProvider provideInvalidIdData
     * @param mixed $invalidId
     */
    public function testShouldNotHandleIdWhenTypeIs($invalidId)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Id must be either an int or a string');

        (new JsonRpcResponse('jsonRpc'))
            ->setId($invalidId);
    }
}
