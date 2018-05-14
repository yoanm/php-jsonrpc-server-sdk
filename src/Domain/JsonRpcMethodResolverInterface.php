<?php
namespace Yoanm\JsonRpcServer\Domain;

/**
 * Class JsonRpcMethodResolverInterface
 */
interface JsonRpcMethodResolverInterface
{
    /**
     * @param string $methodName
     *
     * @return JsonRpcMethodInterface|mixed A valid JSON-RPC method, anything else will be considered as invalid
     */
    public function resolve(string $methodName);
}
