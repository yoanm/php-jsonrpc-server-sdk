<?php
namespace Yoanm\JsonRpcServer\App\Handler;

use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;
use Yoanm\JsonRpcServer\Domain\Event\Action as ActionEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * Class ExceptionHandler
 */
class ExceptionHandler
{
    use JsonRpcServerDispatcherAwareTrait;

    /** @var ResponseCreator */
    private $responseCreator;

    /**
     * @param ResponseCreator $responseCreator
     */
    public function __construct(ResponseCreator $responseCreator)
    {
        $this->responseCreator = $responseCreator;
    }

    /**
     * @param \Exception $exception
     *
     * @return JsonRpcResponse
     */
    public function getJsonRpcResponseFromException(\Exception $exception, JsonRpcRequest $fromRequest = null) : JsonRpcResponse
    {
        $event = new ActionEvent\OnExceptionEvent($exception, $fromRequest);

        $this->dispatchJsonRpcEvent(JsonRpcServerDispatcherInterface::ON_EXCEPTION_EVENT_NAME, $event);

        return $this->responseCreator->createErrorResponse($event->getException(), $fromRequest);
    }
}
