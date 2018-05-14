<?php
namespace Yoanm\JsonRpcServer\Domain;

/**
 * Interface JsonRpcMethodInterface
 */
interface JsonRpcMethodInterface
{
    /**
     * @return mixed Will be json encoded later
     *
     * @throws \Exception In case of failure. Code and message will be used to generate custom JSON-RPC error
     */
    public function apply(array $paramList = null);
}
