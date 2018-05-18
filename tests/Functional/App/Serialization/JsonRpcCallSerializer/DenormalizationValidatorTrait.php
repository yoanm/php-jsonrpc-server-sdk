<?php
namespace Tests\Functional\App\Serialization\JsonRpcCallSerializer;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

trait DenormalizationValidatorTrait
{
    /**
     * @param $isBatch
     * @param $jsonRpcCall
     * @param $decodedContent
     */
    public function assertValidDenormalization(array $decodedContent, JsonRpcCall $jsonRpcCall, bool $isBatch)
    {
        $this->assertSame($isBatch, $jsonRpcCall->isBatch());
        if (!$isBatch) {
            $this->assertCount(1, $jsonRpcCall->getItemList(), 'Item list for non batch request should be only 1');
        } else {
            $this->assertTrue(
                count($jsonRpcCall->getItemList()) >= 1,
                'Item list for batch request should be greater or equal to 1'
            );
            $this->assertCount(
                count($decodedContent),
                $jsonRpcCall->getItemList(),
                'Item list for batch request is not the expected one'
            );
        }
    }
}
