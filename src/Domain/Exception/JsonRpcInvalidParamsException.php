<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcInvalidParamsException
 */
class JsonRpcInvalidParamsException extends JsonRpcException
{
    const CODE = -32602;

    const DATA_VIOLATIONS_KEY = 'violations';

    /**
     * @param array<mixed> $violationMessageList
     */
    public function __construct(array $violationMessageList)
    {
        parent::__construct(
            self::CODE,
            'Invalid params',
            [
                self::DATA_VIOLATIONS_KEY => $violationMessageList,
            ]
        );
    }
}
