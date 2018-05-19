<?php
namespace Tests\Functional\Domain\Event\Acknowledge;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnBatchSubRequestProcessedEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnBatchSubRequestProcessedEvent
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Acknowledge\AbstractOnBatchSubRequestProcessEvent
 *
 * @group Events
 * @group AcknowledgeEvents
 */
class OnBatchSubRequestProcessedEventTest extends TestCase
{
    public function testShouldManageAnItemPosition()
    {
        $itemPosition = 23;

        $event = new OnBatchSubRequestProcessedEvent($itemPosition);

        $this->assertSame($itemPosition, $event->getItemPosition());
    }
}
