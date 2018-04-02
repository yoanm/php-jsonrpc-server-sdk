<?php
namespace Tests\Functional\Infra\RawObject;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawRequest;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawRequest
 */
class JsonRpcRawRequestTest extends TestCase
{
    public function testShouldManageBatchProperty()
    {
        $this->assertTrue((new JsonRpcRawRequest(true))->isBatch());
        $this->assertFalse((new JsonRpcRawRequest(false))->isBatch());
    }

    public function testShouldManageAnItemList()
    {
        $rawRequest = new JsonRpcRawRequest();

        $firstItem = $this->prophesize(JsonRpcRequest::class);
        $secondItem = $this->prophesize(\Exception::class);
        $thirdItem = $this->prophesize(\Exception::class);
        $fourthItem = $this->prophesize(JsonRpcRequest::class);

        $rawRequest->addRequestItem($firstItem->reveal())
            ->addExceptionItem($secondItem->reveal())
            ->addExceptionItem($thirdItem->reveal())
            ->addRequestItem($fourthItem->reveal())
        ;

        $this->assertCount(4, $rawRequest->getItemtList());
        $itemList = $rawRequest->getItemtList();
        $this->assertSame($firstItem->reveal(), $itemList[0]);
        $this->assertSame($secondItem->reveal(), $itemList[1]);
        $this->assertSame($thirdItem->reveal(), $itemList[2]);
        $this->assertSame($fourthItem->reveal(), $itemList[3]);
    }
}
