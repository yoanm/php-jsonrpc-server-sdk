<?php
namespace Tests\Functional\Domain\Event\Action;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\Domain\Event\Action\OnMethodFailureEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Action\OnMethodFailureEvent
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Action\AbstractOnMethodEvent
 *
 * @group Events
 * @group ActionEvents
 */
class OnMethodFailureEventTest extends TestCase
{
    public function testShouldManageAResultFromAMethodAndARequest()
    {
        $exception = new \Exception('my-message');
        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);
        /** @var JsonRpcRequest|ObjectProphecy $request */
        $request = $this->prophesize(JsonRpcRequest::class);

        $event = new OnMethodFailureEvent($exception, $method->reveal(), $request->reveal());

        $this->assertSame($exception, $event->getException());
        $this->assertSame($method->reveal(), $event->getMethod());
        $this->assertSame($request->reveal(), $event->getJsonRpcRequest());
    }

    public function testShouldAllowResultChange()
    {
        $exceptionA = new \Exception('my-message-a');
        $exceptionB = new \Exception('my-message-b');
        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);
        /** @var JsonRpcRequest|ObjectProphecy $request */
        $request = $this->prophesize(JsonRpcRequest::class);

        $event = new OnMethodFailureEvent($exceptionA, $method->reveal(), $request->reveal());

        $this->assertSame($exceptionA, $event->getException());

        $event->setException($exceptionB);
        $this->assertSame($exceptionB, $event->getException());
    }
}
