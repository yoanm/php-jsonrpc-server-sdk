<?php
namespace Yoanm\JsonRpcServer\App\Creator;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;

/**
 * Class CustomExceptionCreator
 */
class CustomExceptionCreator
{
    const MAX_VALID_ERROR_CODE = -32000;
    const MIN_VALID_ERROR_CODE = -32099;

    const ERROR_DATA_PREVIOUS_KEY = 'previous';

    /**
     * @param \Exception $exception
     *
     * @return JsonRpcExceptionInterface
     */
    public function createFor(\Exception $exception) : JsonRpcExceptionInterface
    {
        if ($exception instanceof JsonRpcExceptionInterface) {
            return $exception;
        }
        $errorCode = (int) $exception->getCode();
        if ($errorCode < self::MIN_VALID_ERROR_CODE || $errorCode > self::MAX_VALID_ERROR_CODE) {
            return new JsonRpcInternalErrorException($exception);
        }

        $data = [];

        if ($exception->getPrevious()) {
            $data[self::ERROR_DATA_PREVIOUS_KEY] = $exception->getPrevious();
        }

        return new JsonRpcException($errorCode, $exception->getMessage(), $data);
    }
}
