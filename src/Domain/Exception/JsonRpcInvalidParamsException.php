<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcInvalidParamsException
 */
class JsonRpcInvalidParamsException extends JsonRpcException
{
    const CODE = -32602;

    const DATA_METHOD_KEY = 'method';
    const DATA_MESSAGE_KEY = 'message';

    /**
     * @param string $method
     * @param string $message
     */
    public function __construct(string $method, string $message)
    {
        parent::__construct(
            self::CODE,
            'Invalid params',
            [
                self::DATA_METHOD_KEY => $method,
                self::DATA_MESSAGE_KEY => $message,
            ]
        );
    }
}
