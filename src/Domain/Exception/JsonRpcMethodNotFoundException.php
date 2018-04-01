<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcMethodNotFoundException
 */
class JsonRpcMethodNotFoundException extends JsonRpcException
{
    const CODE = -32601;

    const DATA_METHOD_KEY = 'method';

    /**
     * @param string $methodName
     */
    public function __construct(string $methodName)
    {
        parent::__construct(
            self::CODE,
            'Method not found',
            [
                self::DATA_METHOD_KEY => $methodName,
            ]
        );
    }
}
