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
     * @return JsonRpcMethodInterface|null A valid JSON-RPC method or null if not resolved
     */
    public function resolve(string $methodName) : ?JsonRpcMethodInterface;
}
