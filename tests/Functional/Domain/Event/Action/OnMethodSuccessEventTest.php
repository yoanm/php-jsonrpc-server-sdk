<?php
namespace Tests\Functional\Domain\Event\Action;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\Domain\Event\Action\OnMethodSuccessEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Action\OnMethodSuccessEvent
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Action\AbstractOnMethodEvent
 *
 * @group Events
 * @group ActionEvents
 */
class OnMethodSuccessEventTest extends TestCase
{
    use ProphecyTrait;

    public function testShouldManageAResultFromAMethodAndARequest()
    {
        $result = 'my-result';
        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);
        /** @var JsonRpcRequest|ObjectProphecy $request */
        $request = $this->prophesize(JsonRpcRequest::class);

        $event = new OnMethodSuccessEvent($result, $method->reveal(), $request->reveal());

        $this->assertSame($result, $event->getResult());
        $this->assertSame($method->reveal(), $event->getMethod());
        $this->assertSame($request->reveal(), $event->getJsonRpcRequest());
    }

    public function testShouldAllowResultChange()
    {
        $resultA = 'my-result-a';
        $resultB = 'my-result-b';
        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);
        /** @var JsonRpcRequest|ObjectProphecy $request */
        $request = $this->prophesize(JsonRpcRequest::class);

        $event = new OnMethodSuccessEvent($resultA, $method->reveal(), $request->reveal());

        $this->assertSame($resultA, $event->getResult());

        $event->setResult($resultB);
        $this->assertSame($resultB, $event->getResult());
    }
}
