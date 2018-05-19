<?php
namespace Yoanm\JsonRpcServer\Domain\Model;

/**
 * Class JsonRpcCall
 */
class JsonRpcCall
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
     * @return JsonRpcCall
     */
    public function addRequestItem(JsonRpcRequest $item) : JsonRpcCall
    {
        $this->itemList[] = $item;

        return $this;
    }

    /**
     * @param \Exception $item
     *
     * @return JsonRpcCall
     */
    public function addExceptionItem(\Exception $item) : JsonRpcCall
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
    public function getItemList() : array
    {
        return $this->itemList;
    }
}
