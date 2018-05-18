<?php
namespace Tests\Functional\BehatContext\App\Method;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

abstract class AbstractMethod implements JsonRpcMethodInterface
{
    /**
     * @param array $paramList
     *
     * @return array
     */
    public function validateParams(array $paramList) : array
    {
        return [];
    }
}
