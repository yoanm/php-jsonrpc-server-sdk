<?php
namespace Yoanm\JsonRpcServer\Domain;

/**
 * Interface JsonRpcMethodAwareInterface
 */
interface JsonRpcMethodAwareInterface
{
    /**
     * @param string                 $methodName
     * @param JsonRpcMethodInterface $method
     *
     * @return void
     */
    public function addJsonRpcMethod(string $methodName, JsonRpcMethodInterface $method) : void;
}
