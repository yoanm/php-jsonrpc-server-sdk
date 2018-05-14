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
    /** @var JsonRpcRequest|null */
    private $jsonRpcRequest;
    /** @var JsonRpcMethodInterface */
    private $method;

    /**
     * @param JsonRpcMethodInterface $method
     * @param JsonRpcRequest|null    $jsonRpcRequest
     */
    public function __construct(JsonRpcMethodInterface $method, JsonRpcRequest $jsonRpcRequest = null)
    {
        $this->jsonRpcRequest = $jsonRpcRequest;
        $this->method = $method;
    }

    /**
     * @return null|JsonRpcRequest
     */
    public function getJsonRpcRequest()
    {
        return $this->jsonRpcRequest;
    }

    /**
     * @return JsonRpcMethodInterface
     */
    public function getMethod()
    {
        return $this->method;
    }
}
