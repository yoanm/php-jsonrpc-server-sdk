<?php
namespace Tests\Functional\App\Serialization\JsonRpcCallSerializer;

use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

trait NormalizationHelperTrait
{
    /**
     * @param JsonRpcCallResponse                          $rawResponse
     * @param ObjectProphecy|JsonRpcCallResponseNormalizer $responseNormalizer
     *
     * @return array Expected response
     */
    public function prophesizeJsonRpcCallResponseNormalizerNormalize(
        JsonRpcCallResponse $rawResponse,
        ObjectProphecy $responseNormalizer
    ) {
        $normalizedResponse = ['id' => spl_object_hash($rawResponse)];
        $responseNormalizer->normalize($rawResponse)
            ->willReturn($normalizedResponse)
            ->shouldBeCalled();

        return $normalizedResponse;
    }

    /**
     * @param array|null $normalized
     * @param array      $expectedResponse
     * @param bool       $isBatch
     * @param bool       $expectNull
     */
    public function assertValidNormalization(
        $normalized,
        array $expectedResponse,
        bool $isBatch,
        bool $expectNull
    ) {
        if ($expectNull) {
            $this->assertNull($normalized, 'Normalized response should be null');
        } else {
            $this->assertTrue(is_array($normalized), 'Normalized response should be an array');
            $this->assertSame(count($expectedResponse), count($normalized), 'Result count is not the expected one');
            if ($isBatch) {
                // All items must be a valid response
                foreach ($normalized as $subResponse) {
                    $this->assertSame($subResponse, array_shift($expectedResponse));
                }
            } else {
                $this->assertSame($normalized, $expectedResponse);
            }
        }
    }
}
