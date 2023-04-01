<?php
namespace Tests\Functional\Domain\Event\Action;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Event\Action\OnExceptionEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Action\OnExceptionEvent
 *
 * @group Events
 * @group ActionEvents
 */
class OnExceptionEventTest extends TestCase
{
    use ProphecyTrait;

    public function testShouldManageAnException()
    {
        $exception = new \Exception('my-message');
        $event = new OnExceptionEvent($exception);

        $this->assertSame($exception, $event->getException());
    }

    public function testShouldManageAnOptionalRequest()
    {
        $request = new JsonRpcRequest('jsonrpc', 'method');
        $event = new OnExceptionEvent(new \Exception('my-message'), $request);

        $this->assertSame($request, $event->getFromJsonRpcRequest());
    }

    public function testShouldAllowExceptionChange()
    {
        $exceptionA = new \Exception('my-message-a');
        $exceptionB = new \Exception('my-message-b');
        $event = new OnExceptionEvent($exceptionA);

        $this->assertSame($exceptionA, $event->getException());

        $event->setException($exceptionB);
        $this->assertSame($exceptionB, $event->getException());
    }
}
