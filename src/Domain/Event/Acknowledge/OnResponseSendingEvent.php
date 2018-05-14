<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Acknowledge;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

/**
 * Class OnResponseSendingEvent
 *
 * Dispatched when a response has been successfully serialized by the endpoint and will be returned
 */
class OnResponseSendingEvent implements JsonRpcServerEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.on_response_sending';

    /** @var string */
    private $responseString;
    /** @var JsonRpcCallResponse */
    private $jsonRpcCallResponse;
    /** @var null|JsonRpcCall */
    private $jsonRpcCall = null;

    /**
     * @param string              $responseString
     * @param JsonRpcCallResponse $jsonRpcCallResponse
     * @param JsonRpcCall|null    $jsonRpcCall
     */
    public function __construct(
        string $responseString,
        JsonRpcCallResponse $jsonRpcCallResponse,
        JsonRpcCall $jsonRpcCall = null
    ) {
        $this->responseString = $responseString;
        $this->jsonRpcCallResponse = $jsonRpcCallResponse;
        $this->jsonRpcCall = $jsonRpcCall;
    }

    /**
     * @return string
     */
    public function getResponseString() : string
    {
        return $this->responseString;
    }

    /**
     * @return JsonRpcCallResponse
     */
    public function getJsonRpcCallResponse() : JsonRpcCallResponse
    {
        return $this->jsonRpcCallResponse;
    }

    /**
     * @return null|JsonRpcCall
     */
    public function getJsonRpcCall()
    {
        return $this->jsonRpcCall;
    }
}
