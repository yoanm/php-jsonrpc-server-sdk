<?php
namespace DemoApp\Method;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;

class MethodThatThrowJsonRpcExceptionDuringExecution extends AbstractMethod
{
    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        throw new JsonRpcException(
            -32012,
            'A custom json-rpc error',
            [
                'custom-data-property' => 'custom-data-value'
            ]
        );
    }
}
