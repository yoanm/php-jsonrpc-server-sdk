<?php
namespace Tests\Functional\Domain\Event\Acknowledge;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnRequestReceivedEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnRequestReceivedEvent
 *
 * @group Events
 * @group AcknowledgeEvents
 */
class OnRequestReceivedEventTest extends TestCase
{
    use ProphecyTrait;

    public function testShouldManageACallStringAndDenormalizedCall()
    {
        $callString = 'my-call-string';
        $call = new JsonRpcCall();

        $event = new OnRequestReceivedEvent($callString, $call);

        $this->assertSame($callString, $event->getRequest());
        $this->assertSame($call, $event->getJsonRpcCall());
    }
}
