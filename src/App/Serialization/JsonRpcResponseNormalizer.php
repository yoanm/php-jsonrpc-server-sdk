<?php
namespace Yoanm\JsonRpcServer\App\Serialization;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * Class JsonRpcResponseNormalizer
 */
class JsonRpcResponseNormalizer
{
    const KEY_JSON_RPC = 'jsonrpc';
    const KEY_ID = 'id';
    const KEY_RESULT = 'result';
    const KEY_ERROR = 'error';

    const SUB_KEY_ERROR_CODE = 'code';
    const SUB_KEY_ERROR_MESSAGE = 'message';
    const SUB_KEY_ERROR_DATA = 'data';

    /**
     * @param JsonRpcResponse $response
     *
     * @return array|null
     */
    public function normalize(JsonRpcResponse $response) : ?array
    {
        // Notifications must not have a response, even if they are on error
        if ($response->isNotification()) {
            return null;
        }

        $data = [
            self::KEY_JSON_RPC => $response->getJsonRpc(),
            self::KEY_ID => $response->getId()
        ];

        if ($response->getError()) {
            $data[self::KEY_ERROR] = $this->normalizeError(
                $response->getError()
            );
        } else {
            $data[self::KEY_RESULT] = $response->getResult();
        }

        return $data;
    }

    /**
     * @param JsonRpcExceptionInterface $error
     *
     * @return array
     */
    private function normalizeError(JsonRpcExceptionInterface $error) : array
    {
        $normalizedError = [
            self::SUB_KEY_ERROR_CODE => $error->getErrorCode(),
            self::SUB_KEY_ERROR_MESSAGE => $error->getErrorMessage()
        ];

        if ($error->getErrorData()) {
            $normalizedError[self::SUB_KEY_ERROR_DATA] = $error->getErrorData();
        }

        return $normalizedError;
    }
}
