<?php
namespace Yoanm\JsonRpcServer\Domain\Model;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;

/**
 * Class MethodResolverInterface
 */
interface MethodResolverInterface
{
    /**
     * @param string $methodName
     *
     * @return JsonRpcMethodInterface|mixed A valid JSON-RPC method, anything else will be considered as invalid
     */
    public function resolve(string $methodName);
}
