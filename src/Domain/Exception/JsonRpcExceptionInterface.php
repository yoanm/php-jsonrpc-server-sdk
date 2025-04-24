<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Interface JsonRpcExceptionInterface
 */
interface JsonRpcExceptionInterface extends \Throwable
{
    /**
     * @return int JsonRpc error code
     */
    public function getErrorCode() : int;

    /**
     * @return string JsonRpc error message
     */
    public function getErrorMessage() : string;

    /**
     * @return array Optional error data
     */
    public function getErrorData() : array;
}
