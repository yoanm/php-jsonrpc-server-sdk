<?php
namespace Tests\Technical\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest
 */
class JsonRpcRequestTest extends TestCase
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

        (new JsonRpcRequest('jsonRpc', 'my-method'))
            ->setId($invalidId);
    }
}
