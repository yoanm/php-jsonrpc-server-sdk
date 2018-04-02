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
     * @return JsonRpcMethodInterface
     *
     * @throws JsonRpcMethodNotFoundException In case method was not found
     */
    public function resolve(string $methodName) : JsonRpcMethodInterface;
}
