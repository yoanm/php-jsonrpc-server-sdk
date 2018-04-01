<?php

namespace Yoanm\JsonRpcServer\Infra\Serialization;

use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcParseErrorException;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawRequest;

/**
 * Class RawRequestSerializer
 */
class RawRequestSerializer
{
    const KEY_JSON_RPC = 'json-rpc';
    const KEY_ID = 'id';
    const KEY_METHOD = 'method';
    const KEY_PARAM_LIST = 'params';

    /** @var RequestDenormalizer */
    private $requestDenormalizer;

    /**
     * @param RequestDenormalizer $requestDenormalizer
     */
    public function __construct(RequestDenormalizer $requestDenormalizer)
    {
        $this->requestDenormalizer = $requestDenormalizer;
    }

    /**
     * @param string $content
     *
     * @return JsonRpcRawRequest
     */
    public function deserialize(string $content) : JsonRpcRawRequest
    {
        return $this->denormalize(
            $this->decode($content)
        );
    }

    /**
     * @param string $requestContent
     *
     * @return array Decoded content
     *
     * @throws JsonRpcParseErrorException
     */
    public function decode(string $requestContent) : array
    {
        $decodedContent = json_decode($requestContent, true);

        // Check if parsing is ok => Parse error
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonRpcParseErrorException(
                $requestContent,
                json_last_error(),
                json_last_error_msg()
            );
        }

        // Content must be either an array (normal request) or an array of array (batch request)
        //  => so must be an array
        if (!is_array($decodedContent)) {
            throw new JsonRpcInvalidRequestException($requestContent);
        }

        return $decodedContent;
    }

    /**
     * @param array $decodedContent
     *
     * @return JsonRpcRawRequest
     */
    public function denormalize(array $decodedContent) : JsonRpcRawRequest
    {
        $isBatch = true;
        // Loop over each items
        // -> In case it's a valid batch request -> all keys will have numeric type -> iterations = Number of requests
        // -> In case it's a valid normal request -> all keys will have string type -> iterations = 1 (stopped on #1)
        // => Better performance for normal request (Probably most of requests)
        foreach ($decodedContent as $key => $item) {
            // At least a key is a string (that do not contain an int)
            if (!is_int($key)) {
                $isBatch = false;
                break;
            }
        }

        $rawRequest = new JsonRpcRawRequest($isBatch);

        // convert to array in any cases for simpler use
        $itemList = $isBatch ? $decodedContent : [$decodedContent];
        foreach ($itemList as $item) {
            // Safely denormalize items
            try {
                $rawRequest->addRequestItem(
                    $this->requestDenormalizer->denormalize($item)
                );
            } catch (\Exception $exception) {
                $rawRequest->addExceptionItem($exception);
            }
        }

        return $rawRequest;
    }
}
