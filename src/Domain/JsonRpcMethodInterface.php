<?php
namespace Yoanm\JsonRpcServer\Domain;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;

/**
 * Interface JsonRpcMethodInterface
 */
interface JsonRpcMethodInterface
{
    /**
     * @return mixed
     *
     * @throws \Exception       In case of failure. Code and message will be used to generate custom JSON-RPC error
     * @throws JsonRpcException In case of failure. Exception will be re-thrown as is
     */
    public function apply(array $paramList = null);
}
