<?php
namespace Tests\Functional\Domain\Event\Action;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\Domain\Event\Action\ValidateParamsEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Event\Action\ValidateParamsEvent
 *
 * @group Events
 * @group ActionEvents
 */
class ValidateParamsEventTest extends TestCase
{
    public function testShouldManageMethodAndProvidedParamList()
    {
        $paramList = ['my-param-list'];
        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $event = new ValidateParamsEvent($method->reveal(), $paramList);

        $this->assertSame($method->reveal(), $event->getMethod());
        $this->assertSame($paramList, $event->getParamList());
    }

    public function testShouldAllowViolationAppend()
    {
        $violation = ['my-violation'];
        $paramList = ['my-param-list'];
        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $event = new ValidateParamsEvent($method->reveal(), $paramList);

        $event->addViolation($violation);

        $this->assertSame([$violation], $event->getViolationList());
    }

    public function testShouldAllowViolationListChange()
    {
        $violationList = [['my-violation']];
        $paramList = ['my-param-list'];
        /** @var JsonRpcMethodInterface|ObjectProphecy $method */
        $method = $this->prophesize(JsonRpcMethodInterface::class);

        $event = new ValidateParamsEvent($method->reveal(), $paramList);

        $event->setViolationList($violationList);

        $this->assertSame($violationList, $event->getViolationList());
    }
}
