<?php
namespace Yoanm\JsonRpcServer\App\Serialization;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

/**
 * Class JsonRpcCallResponseNormalizer
 */
class JsonRpcCallResponseNormalizer
{
    /** @var JsonRpcResponseNormalizer */
    private $responseNormalizer;

    /**
     * @param JsonRpcResponseNormalizer $responseNormalizer
     */
    public function __construct(JsonRpcResponseNormalizer $responseNormalizer)
    {
        $this->responseNormalizer = $responseNormalizer;
    }

    /**
     * @param JsonRpcCallResponse $jsonRpcCallResponse
     *
     * @return array|null
     */
    public function normalize(JsonRpcCallResponse $jsonRpcCallResponse)
    {
        $resultList = [];
        foreach ($jsonRpcCallResponse->getResponseList() as $response) {
            // Notifications must not have a response, even if they are on error
            if (!$response->isNotification()) {
                $resultList[] = $this->responseNormalizer->normalize($response);
            }
        }

        // if no result, it means It was either :
        // - a batch call with only notifications
        // - a notification request
        // => return null response in all cases
        if (0 === count($resultList)) {
            return null;
        }

        if (!$jsonRpcCallResponse->isBatch()) {
            return array_shift($resultList);
        }

        return $resultList;
    }
}
