<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Acknowledge;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;

/**
 * Class AbstractOnBatchSubRequestProcessEvent
 */
class AbstractOnBatchSubRequestProcessEvent implements JsonRpcServerEvent
{
    /** @var int */
    private $itemPosition;

    /**
     * @param int $itemPosition
     */
    public function __construct(int $itemPosition)
    {
        $this->itemPosition = $itemPosition;
    }

    /**
     * @return int
     */
    public function getItemPosition() : int
    {
        return $this->itemPosition;
    }
}
