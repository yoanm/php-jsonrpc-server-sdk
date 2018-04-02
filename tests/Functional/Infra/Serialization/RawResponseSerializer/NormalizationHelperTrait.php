<?php
namespace Tests\Functional\Infra\Serialization\RawResponseSerializer;

use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawResponse;

trait NormalizationHelperTrait
{
    /**
     * @param JsonRpcRawResponse                $rawResponse
     * @param ObjectProphecy|ResponseNormalizer $responseNormalizer
     *
     * @return array Expected response list
     */
    public function prophesizeResponseNormalizerNormalize(
        JsonRpcRawResponse $rawResponse,
        ObjectProphecy $responseNormalizer
    ) {
        $expectedResponseList = [];
        foreach ($rawResponse->getResponseList() as $response) {
            $isNotificationResponse = $response->isNotification();
            $isSimpleResponse = !$response->isNotification() && !$rawResponse->isBatch();

            // Notification should not produce any result
            // except if it is a simple request, in that case it should return null
            if (!$isNotificationResponse || $isSimpleResponse) {
                $normalizedResponse = ['id' => spl_object_hash($response)];
                $expectedResponseList[] = $normalizedResponse;
                $responseNormalizer->normalize($response)
                    ->willReturn($normalizedResponse)
                    ->shouldBeCalled();
            }
        }

        return $expectedResponseList;
    }

    /**
     * @param array|null $normalized
     * @param array      $expectedResponseList
     * @param bool       $isBatch
     * @param bool       $expectNull
     */
    public function assertValidNormalization(
        $normalized,
        array $expectedResponseList,
        bool $isBatch,
        bool $expectNull
    ) {
        if ($expectNull) {
            $this->assertNull($normalized, 'Normalized response should be null');
        } else {
            $this->assertTrue(is_array($normalized), 'Normalized response should be an array');
            $this->assertSame(count($expectedResponseList), count($normalized), 'Result count is not the expected one');
            if ($isBatch) {
                // All items must be a valid response
                foreach ($normalized as $subResponse) {
                    $this->assertSame($subResponse, array_shift($expectedResponseList));
                }
            } else {
                $this->assertSame($normalized, array_shift($expectedResponseList));
            }
        }
    }
}
