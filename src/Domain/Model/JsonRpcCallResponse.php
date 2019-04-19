<?php
namespace Yoanm\JsonRpcServer\Domain\Model;

/**
 * Class JsonRpcCallResponse
 */
class JsonRpcCallResponse
{
    /** @var bool */
    private $isBatch;
    /** @var JsonRpcResponse[] */
    private $responseList = [];

    /**
     * @param bool $isBatch
     */
    public function __construct(bool $isBatch = false)
    {
        $this->isBatch = $isBatch;
    }

    /**
     * @param JsonRpcResponse $response
     *
     * @return self
     */
    public function addResponse(JsonRpcResponse $response) : self
    {
        $this->responseList[] = $response;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isBatch() : bool
    {
        return $this->isBatch;
    }

    /**
     * @return JsonRpcResponse[]
     */
    public function getResponseList() : array
    {
        return $this->responseList;
    }
}
