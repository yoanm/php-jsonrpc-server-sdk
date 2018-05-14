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
    /** @var JsonRpcCall */
    private $jsonRpcCall;

    /**
     * @param string              $responseString
     * @param JsonRpcCallResponse $jsonRpcCallResponse
     * @param JsonRpcCall         $jsonRpcCall
     */
    public function __construct(
        string $responseString,
        JsonRpcCallResponse $jsonRpcCallResponse,
        JsonRpcCall $jsonRpcCall
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
     * @return JsonRpcCall
     */
    public function getJsonRpcCall() : JsonRpcCall
    {
        return $this->jsonRpcCall;
    }
}
