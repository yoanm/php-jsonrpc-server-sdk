<?php
namespace Tests\Functional\Infra\Serialization\CallResponseSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Serialization\CallResponseSerializer
 */
class NormalizeTest extends TestCase
{
    use JsonRpcCallResponseProviderTrait;
    use NormalizationHelperTrait;

    /** @var CallResponseSerializer */
    private $callResponseSerializer;
    /** @var ResponseNormalizer|ObjectProphecy */
    private $responseNormalizer;

    protected function setUp()
    {
        $this->responseNormalizer= $this->prophesize(ResponseNormalizer::class);
        $this->callResponseSerializer = new JsonRpcResponseSerializer(
            $this->responseNormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideValidCallResponseData
     * @param JsonRpcCallResponse $callResponse
     * @param bool               $isBatch
     * @param bool               $expectNull
     */
    public function testShouldHandle(JsonRpcCallResponse $callResponse, $isBatch, $expectNull)
    {
        $expectedResponseList = $this->prophesizeResponseNormalizerNormalize(
            $callResponse,
            $this->responseNormalizer
        );

        $normalized = $this->callResponseSerializer->normalize($callResponse);

        $this->assertValidNormalization($normalized, $expectedResponseList, $isBatch, $expectNull);
    }
}
