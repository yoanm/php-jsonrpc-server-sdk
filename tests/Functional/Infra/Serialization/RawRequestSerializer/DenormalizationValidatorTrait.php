<?php
namespace Tests\Functional\Infra\Serialization\RawRequestSerializer;

use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawRequest;

trait DenormalizationValidatorTrait
{
    /**
     * @param $isBatch
     * @param $rawRequest
     * @param $decodedContent
     */
    public function assertValidDenormalization(array $decodedContent, JsonRpcRawRequest $rawRequest, bool $isBatch)
    {
        if (!$isBatch) {
            $this->assertCount(1, $rawRequest->getItemtList(), 'Item list for non batch request should be only 1');
        } else {
            $this->assertTrue(
                count($rawRequest->getItemtList()) >= 1,
                'Item list for batch request should be greater or equal to 1'
            );
            $this->assertCount(
                count($decodedContent),
                $rawRequest->getItemtList(),
                'Item list for batch request is not the expected one'
            );
        }
    }
}
