<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class OnMethodSuccessEvent
 */
class OnMethodSuccessEvent extends AbstractOnMethodEvent
{
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
