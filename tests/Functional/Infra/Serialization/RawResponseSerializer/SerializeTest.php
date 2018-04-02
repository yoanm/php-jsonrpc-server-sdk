<?php
namespace Tests\Functional\Infra\Serialization\RawResponseSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawResponse;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer
 */
class SerializeTest extends TestCase
{
    use JsonRpcRawResponseProviderTrait;
    use NormalizationHelperTrait;

    /** @var RawResponseSerializer */
    private $rawResponseSerializer;
    /** @var ResponseNormalizer|ObjectProphecy */
    private $responseNormalizer;

    protected function setUp()
    {
        $this->responseNormalizer= $this->prophesize(ResponseNormalizer::class);
        $this->rawResponseSerializer = new RawResponseSerializer(
            $this->responseNormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideValidRawResponseData
     * @param JsonRpcRawResponse $rawResponse
     * @param bool               $isBatch
     * @param bool               $expectNull
     */
    public function testShouldHandle(JsonRpcRawResponse $rawResponse, $isBatch, $expectNull)
    {
        $expectedResponseList = $this->prophesizeResponseNormalizerNormalize(
            $rawResponse,
            $this->responseNormalizer
        );

        $serialized = $this->rawResponseSerializer->serialize($rawResponse);

        $this->assertValidNormalization(json_decode($serialized, true), $expectedResponseList, $isBatch, $expectNull);
    }
}
