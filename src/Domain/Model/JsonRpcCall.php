<?php
namespace Yoanm\JsonRpcServer\Domain\Model;

/**
 * Class JsonRpcCall
 */
class JsonRpcCall
{
    /** @var bool */
    private $isBatch;
    /** @var (JsonRpcRequest|\Exception)[] */
    private $itemList = [];

    /**
     * @param bool $isBatch
     */
    public function __construct(bool $isBatch = false)
    {
        $this->isBatch = $isBatch;
    }

    /**
     * @param JsonRpcRequest $item
     *
     * @return self
     */
    public function addRequestItem(JsonRpcRequest $item) : self
    {
        $this->itemList[] = $item;

        return $this;
    }

    /**
     * @param \Exception $item
     *
     * @return self
     */
    public function addExceptionItem(\Exception $item) : self
    {
        $this->itemList[] = $item;

        return $this;
    }

    /**
     * @return bool
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
