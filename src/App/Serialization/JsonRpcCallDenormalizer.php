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
     * @param array<mixed> $decodedContent
     *
     * @return JsonRpcCall
     *
     * @throws \Exception
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
     * @param array<mixed> $decodedContent
     *
     * @return bool
     */
    private function guessBatchOrNot(array $decodedContent) : bool
    {
        $isBatch = (0 !== count($decodedContent));
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
     * @param JsonRpcCall  $jsonRpcCall
     * @param array<mixed> $decodedContent
     *
     * @return void
     *
     * @throws \Exception
     */
    private function populateItem(JsonRpcCall $jsonRpcCall, array $decodedContent) : void
    {
        // convert to array in any cases for simpler use
        $itemList = $jsonRpcCall->isBatch() ? $decodedContent : [$decodedContent];
        foreach ($itemList as $itemPosition => $item) {
            // Safely denormalize items
            try {
                $item = $this->requestDenormalizer->denormalize($item);

                $jsonRpcCall->addRequestItem($item);
            } catch (\Exception $exception) {
                if (false === $jsonRpcCall->isBatch()) {
                    // If it's not a batch call, throw the exception
                    throw $exception;
                }
                // Else populate the item (exception will be managed later
                $jsonRpcCall->addExceptionItem($exception);
            }
        }
    }
}
