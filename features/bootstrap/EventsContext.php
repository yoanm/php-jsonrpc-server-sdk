<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use Tests\Functional\BehatContext\App\BehatRequestLifecycleDispatcher;
use Yoanm\JsonRpcServer\Domain\Event\Action\OnExceptionEvent;
use Yoanm\JsonRpcServer\Domain\Event\Action\OnMethodFailureEvent;
use Yoanm\JsonRpcServer\Domain\Event\Action\OnMethodSuccessEvent;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;

/**
 * Defines application features from the specific context.
 */
class EventsContext implements Context
{
    /** @var null|BehatRequestLifecycleDispatcher */
    private $dispatcher = null;
    /** @var array[]|null */
    private $eventDispatchedList = null;

    /**
     * @return null|JsonRpcServerDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @Given endpoint will use default JsonRpcServerDispatcher
     */
    public function givenEndpointWillUseDefaultJsonRpcServerDispatcher()
    {
        $this->dispatcher = new BehatRequestLifecycleDispatcher();
    }

    /**
     * @Then /^(?P<count>\d+) events? should have been dispatched$/
     */
    public function eventsShouldHaveBeenDispatched($count)
    {
        Assert::assertCount((int)$count, $this->getEventDispatchedList());
    }

    /**
     * @Then a :evenClassName event named :eventName should have been dispatched
     */
    public function aEventNamedShouldHaveBeenDispatched($eventClassName, $eventName)
    {
        $event = $this->shitfDispatchedEvent();

        // Check event class
        $expectedEventClass = $this->getFullyQualifiedEventClass($eventClassName);
        $currentEventClass = get_class($event[1]);
        Assert::assertSame(
            $expectedEventClass,
            $currentEventClass,
            sprintf('Expected event class is "%s" but got "%s"', $eventName, $currentEventClass)
        );

        // Check event name
        Assert::assertSame(
            $eventName,
            $event[0],
            sprintf('Expected name is "%s" but got "%s"', $eventName, $event[0])
        );
    }

    /**
     * @Given /^I will replace "Action\\OnMethodSuccess" result by following json:$/
     */
    public function iWillReplaceOnmethodresultResultByFollowingJson(PyStringNode $string)
    {
        $newResult = json_decode($string);

        $this->dispatcher->addJsonRpcListener(
            OnMethodSuccessEvent::EVENT_NAME,
            function (OnMethodSuccessEvent $event) use ($newResult) {
                $event->setResult($newResult);
            }
        );
    }

    /**
     * @Given /^I will replace "Action\\OnException" exception by an exception with following message:$/
     */
    public function iWillReplaceOnexceptionExceptionByAnExceptionWithFollowingMessage(PyStringNode $newExceptionMessage)
    {
        $this->dispatcher->addJsonRpcListener(
            OnExceptionEvent::EVENT_NAME,
            function (OnExceptionEvent $event) use ($newExceptionMessage) {
                $event->setException(new \Exception($newExceptionMessage->getRaw()));
            }
        );
    }

    /**
     * @Given /^I will replace "Action\\OnException" exception by a "(?P<count>-?\d+)" JSON-RPC exception with following message:$/
     */
    public function iWillReplaceOnexceptionExceptionByAJsonRpcExceptionWithFollowingMessage($jsonRpcErrorCode, PyStringNode $string)
    {
        $this->dispatcher->addJsonRpcListener(
            OnExceptionEvent::EVENT_NAME,
            function (OnExceptionEvent $event) use ($jsonRpcErrorCode, $string) {
                $event->setException(new JsonRpcException($jsonRpcErrorCode, $string->getRaw()));
            }
        );
    }

    /**
     * @Given /^I will replace "Action\\OnMethodFailure" exception by an exception with following message:$/
     */
    public function iWillReplaceOnmethodfailureExceptionByAnExceptionWithFollowingMessage(PyStringNode $newExceptionMessage)
    {
        $this->dispatcher->addJsonRpcListener(
            OnMethodFailureEvent::EVENT_NAME,
            function (OnMethodFailureEvent $event) use ($newExceptionMessage) {
                $event->setException(new \Exception($newExceptionMessage->getRaw()));
            }
        );
    }

    /**
     * @Given /^I will replace "Action\\OnMethodFailure" exception by a "(?P<count>-?\d+)" JSON-RPC exception with following message:$/
     */
    public function iWillReplaceOnmethodfailureExceptionByAJsonRpcExceptionWithFollowingMessage($jsonRpcErrorCode, PyStringNode $string)
    {
        $this->dispatcher->addJsonRpcListener(
            OnMethodFailureEvent::EVENT_NAME,
            function (OnMethodFailureEvent $event) use ($jsonRpcErrorCode, $string) {
                $event->setException(new JsonRpcException($jsonRpcErrorCode, $string->getRaw()));
            }
        );
    }

    /**
     * @return array
     */
    protected function getEventDispatchedList()
    {
        if (null === $this->eventDispatchedList) {
            // Backup event list (checks regarding event dispatched must be done after execution not during execution)
            $this->eventDispatchedList = $this->dispatcher->getEventDispatchedList();
        }

        return $this->eventDispatchedList;
    }

    /**
     * @return array
     */
    protected function shitfDispatchedEvent()
    {
        $list = $this->getEventDispatchedList();

        $event = array_shift($list);

        // set the new list
        $this->eventDispatchedList = $list;

        return $event;
    }

    /**
     * @param $eventClassName
     * @return string
     */
    protected function getFullyQualifiedEventClass($eventClassName)
    {
        return sprintf('Yoanm\JsonRpcServer\Domain\Event\\%sEvent', $eventClassName);
    }
}
