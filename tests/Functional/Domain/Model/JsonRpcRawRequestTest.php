<?php
namespace Tests\Functional\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall
 *
 * @group Models
 */
class JsonRpcCallTest extends TestCase
{
    public function testShouldManageBatchProperty()
    {
        $this->assertTrue((new JsonRpcCall(true))->isBatch());
        $this->assertFalse((new JsonRpcCall(false))->isBatch());
    }

    public function testShouldManageAnItemList()
    {
        $jsonRpcCall = new JsonRpcCall();

        $firstItem = $this->prophesize(JsonRpcRequest::class);
        $secondItem = $this->prophesize(\Exception::class);
        $thirdItem = $this->prophesize(\Exception::class);
        $fourthItem = $this->prophesize(JsonRpcRequest::class);

        $jsonRpcCall->addRequestItem($firstItem->reveal())
            ->addExceptionItem($secondItem->reveal())
            ->addExceptionItem($thirdItem->reveal())
            ->addRequestItem($fourthItem->reveal())
        ;

        $this->assertCount(4, $jsonRpcCall->getItemList());
        $itemList = $jsonRpcCall->getItemList();
        $this->assertSame($firstItem->reveal(), $itemList[0]);
        $this->assertSame($secondItem->reveal(), $itemList[1]);
        $this->assertSame($thirdItem->reveal(), $itemList[2]);
        $this->assertSame($fourthItem->reveal(), $itemList[3]);
    }
}
