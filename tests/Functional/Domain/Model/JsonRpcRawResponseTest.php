<?php
namespace Tests\Functional\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse
 */
class JsonRpcCallResponseTest extends TestCase
{
    public function testShouldManageBatchProperty()
    {
        $this->assertTrue((new JsonRpcCallResponse(true))->isBatch());
        $this->assertFalse((new JsonRpcCallResponse(false))->isBatch());
    }

    public function testShouldManageAnItemList()
    {
        $jsonRpcCallResponse = new JsonRpcCallResponse();

        $firstItem = $this->prophesize(JsonRpcResponse::class);
        $secondItem = $this->prophesize(JsonRpcResponse::class);
        $thirdItem = $this->prophesize(JsonRpcResponse::class);

        $jsonRpcCallResponse->addResponse($firstItem->reveal())
            ->addResponse($secondItem->reveal())
            ->addResponse($thirdItem->reveal())
        ;

        $this->assertCount(3, $jsonRpcCallResponse->getResponseList());
        $itemList = $jsonRpcCallResponse->getResponseList();
        $this->assertSame($firstItem->reveal(), $itemList[0]);
        $this->assertSame($secondItem->reveal(), $itemList[1]);
        $this->assertSame($thirdItem->reveal(), $itemList[2]);
    }
}
