<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Acknowledge;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

/**
 * Class OnResponseSendingEvent
 */
class OnResponseSendingEvent implements JsonRpcServerEvent
{
    /** @var string */
    private $responseString;
    /** @var JsonRpcCallResponse */
    private $jsonRpcCallResponse;
    /** @var JsonRpcCall|null */
    private $jsonRpcCall;

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
