<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Acknowledge;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

/**
 * Class OnRequestReceivedEvent
 *
 * Dispatched when a request has been passed to the endpoint and successfully deserialized
 */
class OnRequestReceivedEvent implements JsonRpcServerEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.on_request_received';

    /** @var string */
    private $request;
    /** @var JsonRpcCall */
    private $jsonRpcCall;

    /**
     * @param string      $request
     * @param JsonRpcCall $jsonRpcCall
     */
    public function __construct(string $request, JsonRpcCall $jsonRpcCall)
    {
        $this->request = $request;
        $this->jsonRpcCall = $jsonRpcCall;
    }

    /**
     * @return string
     */
    public function getRequest() : string
    {
        return $this->request;
    }

    /**
     * @return JsonRpcCall
     */
    public function getJsonRpcCall() : JsonRpcCall
    {
        return $this->jsonRpcCall;
    }
}
