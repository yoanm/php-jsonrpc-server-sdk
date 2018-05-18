<?php
namespace Tests\Functional\Infra\Serialization\CallResponseSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;
use Yoanm\JsonRpcServer\Infra\Serialization\CallResponseSerializer;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Serialization\CallResponseSerializer
 */
class SerializeTest extends TestCase
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
        $this->callResponseSerializer = new CallResponseSerializer(
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

        $serialized = $this->callResponseSerializer->serialize($callResponse);

        $this->assertValidNormalization(json_decode($serialized, true), $expectedResponseList, $isBatch, $expectNull);
    }
}
