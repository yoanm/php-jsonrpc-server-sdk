<?php
namespace Yoanm\JsonRpcServer\Domain;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Interface JsonRpcMethodParamsValidatorInterface
 */
interface JsonRpcMethodParamsValidatorInterface
{
    /**
     * @param JsonRpcRequest $jsonRpcRequest
     * @param JsonRpcMethodInterface $method
     *
     * @return array An array of violations
     */
    public function validate(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method) : array;
}
