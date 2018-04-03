<?php
namespace Yoanm\JsonRpcServer\Infra\Serialization;

use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawResponse;

/**
 * Class RawResponseSerializer
 */
class RawResponseSerializer
{
    /** @var ResponseNormalizer */
    private $responseNormalizer;

    /**
     * @param ResponseNormalizer $responseNormalizer
     */
    public function __construct(ResponseNormalizer $responseNormalizer)
    {
        $this->responseNormalizer = $responseNormalizer;
    }

    /**
     * @param JsonRpcRawResponse $rawReponse
     *
     * @return string
     */
    public function serialize(JsonRpcRawResponse $rawReponse) : string
    {
        return $this->encode(
            $this->normalize($rawReponse)
        );
    }

    /**
     * @param mixed $normalizedContent
     *
     * @return string
     */
    public function encode($normalizedContent) : string
    {
        return json_encode($normalizedContent);
    }

    /**
     * @param JsonRpcRawResponse $rawResponse
     *
     * @return array|null
     */
    public function normalize(JsonRpcRawResponse $rawResponse)
    {
        if ($rawResponse->isBatch()) {
            return $this->normalizeBatchResponse($rawResponse->getResponseList());
        } else {
            $responseList = $rawResponse->getResponseList();

            return $this->responseNormalizer->normalize(array_shift($responseList));
        }
    }

    /**
     * @param JsonRpcResponse[] $responseList
     *
     * @return array|null
     */
    private function normalizeBatchResponse(array $responseList)
    {
        $resultList = [];
        foreach ($responseList as $response) {
            // Notifications must not have a response, even if they are on error
            if (!$response->isNotification()) {
                $resultList[] = $this->responseNormalizer->normalize($response);
            }
        }

        // if no result, it means It was a batch call with only notifications => return null response
        return count($resultList) > 0
            ? $resultList
            : null
        ;
    }
}
