<?php
namespace Yoanm\JsonRpcServer\Infra\Endpoint;

use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge as AcknowledgeEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * Class JsonRpcEndpoint
 */
class JsonRpcEndpoint
{
    use JsonRpcServerDispatcherAwareTrait;

    /** @var JsonRpcCallSerializer */
    private $jsonRpcCallSerializer;
    /** @var JsonRpcRequestHandler */
    private $jsonRpcRequestHandler;
    /** @var ExceptionHandler */
    private $exceptionHandler;

    /**
     * @param JsonRpcCallSerializer $jsonRpcCallSerializer
     * @param JsonRpcRequestHandler $jsonRpcRequestHandler
     */
    public function __construct(
        JsonRpcCallSerializer $jsonRpcCallSerializer,
        JsonRpcRequestHandler $jsonRpcRequestHandler,
        ExceptionHandler $exceptionHandler
    ) {
        $this->jsonRpcCallSerializer = $jsonRpcCallSerializer;
        $this->jsonRpcRequestHandler = $jsonRpcRequestHandler;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * @param string $request
     *
     * @return string The response
     */
    public function index(string $request) : string
    {
        $jsonRpcCall = null;
        try {
            $jsonRpcCall = $this->getJsonRpcCall($request);

            $jsonRpcCallResponse = $this->getJsonRpcCallResponse($jsonRpcCall);

            return $this->getResponseString($jsonRpcCallResponse, $jsonRpcCall);
        } catch (\Exception $exception) {
            // Try to create a valid json-rpc error
            $jsonRpcCallResponse = (new JsonRpcCallResponse())->addResponse(
                $this->exceptionHandler->getJsonRpcResponseFromException($exception)
            );

            return $this->getResponseString($jsonRpcCallResponse, $jsonRpcCall);
        }
    }

    /**
     * @param string $request
     *
     * @return JsonRpcCall
     */
    protected function getJsonRpcCall(string $request) : JsonRpcCall
    {
        $jsonRpcCall = $this->jsonRpcCallSerializer->deserialize($request);

        $this->dispatchJsonRpcEvent(
            JsonRpcServerDispatcherInterface::ON_REQUEST_RECEIVED_EVENT_NAME,
            new AcknowledgeEvent\OnRequestReceivedEvent($request, $jsonRpcCall)
        );

        return $jsonRpcCall;
    }

    /**
     * @param JsonRpcCallResponse     $jsonRpcCallResponse
     * @param JsonRpcCall|null        $jsonRpcCall
     *
     * @return string
     */
    protected function getResponseString(JsonRpcCallResponse $jsonRpcCallResponse, JsonRpcCall $jsonRpcCall = null) : string
    {
        $response = $this->jsonRpcCallSerializer->serialize($jsonRpcCallResponse);

        $this->dispatchJsonRpcEvent(
            JsonRpcServerDispatcherInterface::ON_RESPONSE_SENDING_EVENT_NAME,
            new AcknowledgeEvent\OnResponseSendingEvent($response, $jsonRpcCallResponse, $jsonRpcCall)
        );

        return $response;
    }

    /**
     * @param JsonRpcCall $jsonRpcCall
     *
     * @return JsonRpcCallResponse
     */
    protected function getJsonRpcCallResponse(JsonRpcCall $jsonRpcCall) : JsonRpcCallResponse
    {
        $jsonRpcCallResponse = new JsonRpcCallResponse($jsonRpcCall->isBatch());

        foreach ($jsonRpcCall->getItemList() as $itemPosition => $item) {
            if ($jsonRpcCall->isBatch()) {
                $this->dispatchJsonRpcEvent(
                    JsonRpcServerDispatcherInterface::ON_BATCH_SUB_REQUEST_PROCESSING_EVENT_NAME,
                    new AcknowledgeEvent\OnBatchSubRequestProcessingEvent($itemPosition)
                );
            }
            $jsonRpcCallResponse->addResponse(
                $this->processItem($item)
            );
            if ($jsonRpcCall->isBatch()) {
                $this->dispatchJsonRpcEvent(
                    JsonRpcServerDispatcherInterface::ON_BATCH_SUB_REQUEST_PROCESSED_EVENT_NAME,
                    new AcknowledgeEvent\OnBatchSubRequestProcessedEvent($itemPosition)
                );
            }
        }

        return $jsonRpcCallResponse;
    }

    /**
     * @param JsonRpcRequest|\Exception $item
     *
     * @return JsonRpcResponse
     */
    private function processItem($item) : JsonRpcResponse
    {
        try {
            if ($item instanceof \Exception) {
                throw $item;
            }

            return $this->jsonRpcRequestHandler->processJsonRpcRequest($item);
        } catch (\Exception $exception) {
            return $this->exceptionHandler->getJsonRpcResponseFromException(
                $exception,
                $item instanceof JsonRpcRequest ? $item : null
            );
        }
    }
}
