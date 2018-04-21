<?php
namespace Yoanm\JsonRpcServer\Domain\Model;

/**
 * Interface JsonRpcMethodInterface
 */
interface JsonRpcMethodInterface
{
    /**
     * @param array $paramList
     *
     * @return array List of violations, will be used later to generate proper JSON-RPC error
     */
    public function validateParams(array $paramList) : array;

    /**
     * @param array|null $paramList
     *
     * @return mixed Will be json encoded later
     *
     * @throws \Exception In case of failure. Code and message will be used to generate custom JSON-RPC error
     */
    public function apply(array $paramList = null);
}
