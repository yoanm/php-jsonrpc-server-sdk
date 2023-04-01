<?php
namespace Tests\Functional\Domain\Event\Acknowledge;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnBatchSubRequestProcessedEvent;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnBatchSubRequestProcessedEvent
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Acknowledge\AbstractOnBatchSubRequestProcessEvent
 *
 * @group Events
 * @group AcknowledgeEvents
 */
class OnBatchSubRequestProcessedEventTest extends TestCase
{
    use ProphecyTrait;

    public function testShouldManageAnItemPosition()
    {
        $itemPosition = 23;

        $event = new OnBatchSubRequestProcessedEvent($itemPosition);

        $this->assertSame($itemPosition, $event->getItemPosition());
    }
}
