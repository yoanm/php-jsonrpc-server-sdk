<?php
namespace Yoanm\JsonRpcServer\App\Serialization;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

/**
 * Class JsonRpcCallDenormalizer
 */
class JsonRpcCallDenormalizer
{
    /** @var JsonRpcRequestDenormalizer */
    private $requestDenormalizer;

    /**
     * @param JsonRpcRequestDenormalizer $requestDenormalizer
     */
    public function __construct(JsonRpcRequestDenormalizer $requestDenormalizer)
    {
        $this->requestDenormalizer = $requestDenormalizer;
    }

    /**
     * @param array $decodedContent
     *
     * @return JsonRpcCall
     */
    public function denormalize(array $decodedContent) : JsonRpcCall
    {
        $jsonRpcCall = new JsonRpcCall(
            $this->guessBatchOrNot($decodedContent)
        );

        $this->populateItem($jsonRpcCall, $decodedContent);

        return $jsonRpcCall;
    }

    /**
     * @param array $decodedContent
     *
     * @return bool
     */
    private function guessBatchOrNot(array $decodedContent) : bool
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

        return $isBatch;
    }

    /**
     * @param JsonRpcCall $jsonRpcCall
     * @param array       $decodedContent
     */
    private function populateItem(JsonRpcCall $jsonRpcCall, array $decodedContent)
    {
        // convert to array in any cases for simpler use
        $itemList = $jsonRpcCall->isBatch() ? $decodedContent : [$decodedContent];
        foreach ($itemList as $itemPosition => $item) {
            // Safely denormalize items
            try {
                $item = $this->requestDenormalizer->denormalize($item);

                $jsonRpcCall->addRequestItem($item);
            } catch (\Exception $exception) {
                $jsonRpcCall->addExceptionItem($exception);
            }
        }
    }
}
