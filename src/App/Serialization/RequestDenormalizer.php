<?php

namespace Yoanm\JsonRpcServer\App\Serialization;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class RequestDenormalizer
 */
class RequestDenormalizer
{

    const KEY_JSON_RPC = 'json-rpc';
    const KEY_ID = 'id';
    const KEY_METHOD = 'method';
    const KEY_PARAM_LIST = 'params';

    /**
     * @param mixed $item Should be an array or an instance of \stdClass
     *
     * @return JsonRpcRequest
     *
     * @throws JsonRpcInvalidRequestException
     */
    public function denormalize($item) : JsonRpcRequest
    {
        $this->validateArray($item, 'Item must be an array');

        // Validate json-rpc and method keys
        $this->validateRequiredKey($item, self::KEY_JSON_RPC);
        $this->validateRequiredKey($item, self::KEY_METHOD);

        $request = new JsonRpcRequest($item[self::KEY_JSON_RPC], $item[self::KEY_METHOD]);

        /** If no id defined => request is a notification */
        if (isset($item[self::KEY_ID])) {
            $request->setId(
                $item[self::KEY_ID] == (string) ((int) $item[self::KEY_ID])
                    ? (int) $item[self::KEY_ID] // Convert it in case it's a string containing an int
                    : (string) $item[self::KEY_ID] // Convert to string in all other cases
            );
        }

        if (isset($item[self::KEY_PARAM_LIST])) {
            $paramList = $item[self::KEY_PARAM_LIST];
            $this->validateArray($paramList, 'Parameter list must be an array');
            $request->setParamList($paramList);
        }

        return $request;
    }

    /**
     * @param mixed  $value
     * @param string $errorDescription
     *
     * @return array
     *
     * @throws JsonRpcInvalidRequestException
     */
    private function validateArray($value, string $errorDescription) : array
    {
        if (!is_array($value)) {
            throw new JsonRpcInvalidRequestException($value, $errorDescription);
        }

        return $value;
    }

    /**
     * @param array  $item
     * @param string $key
     *
     * @throws JsonRpcInvalidRequestException
     */
    private function validateRequiredKey(array $item, string $key)
    {
        if (!isset($item[$key])) {
            throw new JsonRpcInvalidRequestException(
                $item,
                sprintf('"%s" is a required key', $key)
            );
        }
    }
}
