<?php
namespace Yoanm\JsonRpcServer\Infra\RawObject;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * Class JsonRpcRawResponse
 */
class JsonRpcRawResponse
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
     * @return JsonRpcRawResponse
     */
    public function addResponse(JsonRpcResponse $response) : JsonRpcRawResponse
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
