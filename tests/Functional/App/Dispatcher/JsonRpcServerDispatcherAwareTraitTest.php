<?php
namespace Tests\Functional\App\Dispatcher;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;
use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;

/**
 * @covers \Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait
 *
 * @group JsonRpcServerDispatcherAwareTrait
 */
class JsonRpcServerDispatcherAwareTraitTest extends TestCase
{
    /** @var JsonRpcServerDispatcherAwareTrait|ConcreteDispatcherAware */
    private $dispatcherAware;
    /** @var JsonRpcServerDispatcherInterface|ObjectProphecy */
    private $jsonRpcServerDispatcher;

    public function setUp()
    {
        $this->jsonRpcServerDispatcher = $this->prophesize(JsonRpcServerDispatcherInterface::class);

        $this->dispatcherAware = new ConcreteDispatcherAware();
    }

    public function testShouldDoNothingIfDispatcherNotProvided()
    {
        $this->assertNull(
            $this->dispatcherAware->testDispatchJsonRpcEvent(
                'event-name',
                $this->prophesize(JsonRpcServerEvent::class)->reveal()
            )
        );
    }

    public function testShouldCallDispatcherIfProvided()
    {
        $eventName = 'event-name';
        /** @var JsonRpcServerEvent|ObjectProphecy $event */
        $event = $this->prophesize(JsonRpcServerEvent::class);

        $this->dispatcherAware->setJsonRpcServerDispatcher($this->jsonRpcServerDispatcher->reveal());

        $this->jsonRpcServerDispatcher->dispatchJsonRpcEvent($eventName, $event->reveal())
            ->shouldBeCalled();

        $this->assertNull($this->dispatcherAware->testDispatchJsonRpcEvent($eventName, $event->reveal()));
    }
}
