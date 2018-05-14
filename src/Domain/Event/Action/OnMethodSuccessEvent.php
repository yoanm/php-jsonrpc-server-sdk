<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class OnMethodSuccessEvent
 *
 * Dispatched only in case JSON-RPC method return a response
 */
class OnMethodSuccessEvent extends AbstractOnMethodEvent
{
    const EVENT_NAME= 'json_rpc_server_skd.on_method_success';

    /** @var mixed|array|null */
    private $result;

    /**
     * @param mixed                  $result
     * @param JsonRpcMethodInterface $method
     * @param JsonRpcRequest|null    $jsonRpcRequest
     */
    public function __construct(
        $result,
        JsonRpcMethodInterface $method,
        JsonRpcRequest $jsonRpcRequest = null
    ) {
        $this->result = $result;

        parent::__construct($method, $jsonRpcRequest);
    }

    /**
     * @return mixed|array|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array|mixed|null $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
