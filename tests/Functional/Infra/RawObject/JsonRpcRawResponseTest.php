<?php
namespace Tests\Functional\Infra\RawObject;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawResponse;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawResponse
 */
class JsonRpcRawResponseTest extends TestCase
{
    public function testShouldManageBatchProperty()
    {
        $this->assertTrue((new JsonRpcRawResponse(true))->isBatch());
        $this->assertFalse((new JsonRpcRawResponse(false))->isBatch());
    }

    public function testShouldManageAnItemList()
    {
        $rawRequest = new JsonRpcRawResponse();

        $firstItem = $this->prophesize(JsonRpcResponse::class);
        $secondItem = $this->prophesize(JsonRpcResponse::class);
        $thirdItem = $this->prophesize(JsonRpcResponse::class);

        $rawRequest->addResponse($firstItem->reveal())
            ->addResponse($secondItem->reveal())
            ->addResponse($thirdItem->reveal())
        ;

        $this->assertCount(3, $rawRequest->getResponseList());
        $itemList = $rawRequest->getResponseList();
        $this->assertSame($firstItem->reveal(), $itemList[0]);
        $this->assertSame($secondItem->reveal(), $itemList[1]);
        $this->assertSame($thirdItem->reveal(), $itemList[2]);
    }
}
