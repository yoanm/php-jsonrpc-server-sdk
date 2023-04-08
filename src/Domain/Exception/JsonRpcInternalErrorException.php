<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * JsonRpcInternalErrorException represents unhandled error during JsonRpc method processing (e.g. "Internal server error").
 */
class JsonRpcInternalErrorException extends JsonRpcException
{
    const CODE = -32603;

    /**
     * @deprecated no longer in use, will be removed at next major release.
     */
    const DATA_PREVIOUS_KEY = 'previous';

    /**
     * @param \Throwable|null $previousException
     */
    public function __construct(\Throwable $previousException = null)
    {
        parent::__construct(
            self::CODE,
            'Internal error',
            [],
            $previousException
        );
    }
}
