<?php
namespace Yoanm\JsonRpcServer\App\Serialization;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * Class JsonRpcRequestDenormalizer
 */
class JsonRpcRequestDenormalizer
{
    const KEY_JSON_RPC = 'jsonrpc';
    const KEY_ID = 'id';
    const KEY_METHOD = 'method';
    const KEY_PARAM_LIST = 'params';

    /**
     * @param mixed $item Should be an array
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

        $request = new JsonRpcRequest(
            $item[self::KEY_JSON_RPC],
            $item[self::KEY_METHOD]
        );

        $this->bindIdIfProvided($request, $item);
        $this->bindParamListIfProvided($request, $item);

        return $request;
    }

    /**
     * @param JsonRpcRequest $request
     * @param array $item
     *
     * @return void
     */
    protected function bindIdIfProvided(JsonRpcRequest $request, array $item) : void
    {
        /** If no id defined => request is a notification */
        if (isset($item[self::KEY_ID])) {
            $request->setId(
                $item[self::KEY_ID] == (string)((int)$item[self::KEY_ID])
                    ? (int)$item[self::KEY_ID] // Convert it in case it's a string containing an int
                    : (string)$item[self::KEY_ID] // Convert to string in all other cases
            );
        }
    }

    /**
     * @param JsonRpcRequest $request
     * @param array          $item
     *
     * @return void
     *
     * @throws JsonRpcInvalidRequestException
     */
    protected function bindParamListIfProvided(JsonRpcRequest $request, array $item) : void
    {
        if (isset($item[self::KEY_PARAM_LIST])) {
            $this->validateArray($item[self::KEY_PARAM_LIST], 'Parameter list must be an array');
            $request->setParamList($item[self::KEY_PARAM_LIST]);
        }
    }

    /**
     * @param mixed  $value
     * @param string $errorDescription
     *
     * @return void
     *
     * @throws JsonRpcInvalidRequestException
     */
    private function validateArray($value, string $errorDescription) : void
    {
        if (!is_array($value)) {
            throw new JsonRpcInvalidRequestException($value, $errorDescription);
        }
    }

    /**
     * @param array  $item
     * @param string $key
     *
     * @return void
     *
     * @throws JsonRpcInvalidRequestException
     */
    private function validateRequiredKey(array $item, string $key) : void
    {
        if (!isset($item[$key])) {
            throw new JsonRpcInvalidRequestException(
                $item,
                sprintf('"%s" is a required key', $key)
            );
        }
    }
}
