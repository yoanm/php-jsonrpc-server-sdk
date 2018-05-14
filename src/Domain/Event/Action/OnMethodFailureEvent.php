<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Action;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class OnMethodFailureEvent
 */
class OnMethodFailureEvent extends AbstractOnMethodEvent
{
    /** @var \Exception */
    private $exception;

    /**
     * @param \Exception             $exception
     * @param JsonRpcMethodInterface $method
     * @param JsonRpcRequest|null    $jsonRpcRequest
     */
    public function __construct(
        \Exception $exception,
        JsonRpcMethodInterface $method,
        JsonRpcRequest $jsonRpcRequest = null
    ) {
        $this->exception = $exception;

        parent::__construct($method, $jsonRpcRequest);
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }
}
