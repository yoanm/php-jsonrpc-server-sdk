<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

/**
 * Class ValidateParamsEvent
 *
 * Dispatched before JSON-RPC will be called, in order to validate params
 */
class ValidateParamsEvent implements JsonRpcServerEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.validate_params';

    /** @var JsonRpcMethodInterface */
    private $method;
    /** @var array */
    private $paramList;
    /** @var array */
    private $violationList = [];

    /**
     * @param JsonRpcMethodInterface $method
     * @param array                  $paramList
     */
    public function __construct(JsonRpcMethodInterface $method, array $paramList)
    {
        $this->method = $method;
        $this->paramList = $paramList;
    }

    /**
     * @return JsonRpcMethodInterface
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParamList() : array
    {
        return $this->paramList;
    }

    /**
     * @param array $violationList
     *
     * @return ValidateParamsEvent
     */
    public function setViolationList(array $violationList) : ValidateParamsEvent
    {
        $this->violationList = $violationList;

        return $this;
    }

    /**
     * @return array
     */
    public function getViolationList()
    {
        return $this->violationList;
    }
}
