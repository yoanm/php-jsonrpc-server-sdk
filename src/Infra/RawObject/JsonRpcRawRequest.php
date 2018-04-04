<?php
namespace Yoanm\JsonRpcServer\Infra\RawObject;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class JsonRpcRawRequest
 */
class JsonRpcRawRequest
{
    /** @var bool */
    private $isBatch;
    /** @var mixed[] */
    private $itemList = [];

    /**
     * @param bool|false $isBatch
     */
    public function __construct(bool $isBatch = false)
    {
        $this->isBatch = $isBatch;
    }

    /**
     * @param JsonRpcRequest $item
     *
     * @return JsonRpcRawRequest
     */
    public function addRequestItem(JsonRpcRequest $item) : JsonRpcRawRequest
    {
        $this->itemList[] = $item;

        return $this;
    }

    /**
     * @param \Exception $item
     *
     * @return JsonRpcRawRequest
     */
    public function addExceptionItem(\Exception $item) : JsonRpcRawRequest
    {
        $this->itemList[] = $item;

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
     * @return (JsonRpcRequest|\Exception)[]
     */
    public function getItemtList() : array
    {
        return $this->itemList;
    }
}
