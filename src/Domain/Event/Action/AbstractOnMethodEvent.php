<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class AbstractOnMethodEvent
 */
abstract class AbstractOnMethodEvent implements JsonRpcServerEvent
{
    /** @var JsonRpcRequest */
    private $jsonRpcRequest;
    /** @var JsonRpcMethodInterface */
    private $method;

    /**
     * @param JsonRpcMethodInterface $method
     * @param JsonRpcRequest         $jsonRpcRequest
     */
    public function __construct(JsonRpcMethodInterface $method, JsonRpcRequest $jsonRpcRequest)
    {
        $this->jsonRpcRequest = $jsonRpcRequest;
        $this->method = $method;
    }

    /**
     * @return JsonRpcRequest
     */
    public function getJsonRpcRequest() : JsonRpcRequest
    {
        return $this->jsonRpcRequest;
    }

    /**
     * @return JsonRpcMethodInterface
     */
    public function getMethod() : JsonRpcMethodInterface
    {
        return $this->method;
    }
}
