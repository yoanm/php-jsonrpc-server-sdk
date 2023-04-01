<?php
namespace Tests\Functional\Domain\Event\Acknowledge;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnBatchSubRequestProcessingEvent;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnBatchSubRequestProcessingEvent
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Acknowledge\AbstractOnBatchSubRequestProcessEvent
 *
 * @group Events
 * @group AcknowledgeEvents
 */
class OnBatchSubRequestProcessingEventTest extends TestCase
{
    use ProphecyTrait;

    public function testShouldManageAnItemPosition()
    {
        $itemPosition = 23;

        $event = new OnBatchSubRequestProcessingEvent($itemPosition);

        $this->assertSame($itemPosition, $event->getItemPosition());
    }
}
