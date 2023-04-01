<?php
namespace Tests\Functional\Domain\Event\Acknowledge;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnResponseSendingEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnResponseSendingEvent
 *
 * @group Events
 * @group AcknowledgeEvents
 */
class OnResponseSendingEventTest extends TestCase
{
    use ProphecyTrait;

    public function testShouldManageAResponseStringAndNormalizedResponseCall()
    {
        $responseString = 'my-call-string';
        $callResponse = new JsonRpcCallResponse();

        $event = new OnResponseSendingEvent($responseString, $callResponse);

        $this->assertSame($responseString, $event->getResponseString());
        $this->assertSame($callResponse, $event->getJsonRpcCallResponse());
    }

    public function testShouldManageOptionalDenormalizedCall()
    {
        $responseString = 'my-call-string';
        $call = new JsonRpcCall();
        $callResponse = new JsonRpcCallResponse();

        $event = new OnResponseSendingEvent($responseString, $callResponse, $call);

        $this->assertSame($call, $event->getJsonRpcCall());
    }
}
