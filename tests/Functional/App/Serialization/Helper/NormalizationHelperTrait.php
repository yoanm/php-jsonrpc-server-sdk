<?php
namespace Tests\Functional\App\Serialization\Helper;

trait NormalizationHelperTrait
{
    /**
     * @param array|null $normalized
     * @param array|null $expectedResponse
     * @param bool       $isBatch
     * @param bool       $expectNull
     */
    public function assertValidNormalization(
        $normalized,
        $expectedResponse,
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
