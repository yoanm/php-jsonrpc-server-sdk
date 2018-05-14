<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Acknowledge;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

/**
 * Class OnRequestReceivedEvent
 */
class OnRequestReceivedEvent implements JsonRpcServerEvent
{
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
