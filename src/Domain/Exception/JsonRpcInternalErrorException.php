<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcInternalErrorException
 */
class JsonRpcInternalErrorException extends JsonRpcException
{
    const CODE = -32603;

    const DATA_PREVIOUS_KEY = 'previous';

    /**
     * @param \Exception|null $previousException
     */
    public function __construct(\Exception $previousException = null)
    {
        parent::__construct(
            self::CODE,
            'Internal error',
            $previousException ? [self::DATA_PREVIOUS_KEY => $previousException] : []
        );
    }
}
