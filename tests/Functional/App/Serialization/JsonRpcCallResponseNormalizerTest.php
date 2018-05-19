<?php
namespace Tests\Functional\App\Serialization;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\Functional\App\Serialization\Helper\JsonRpcCallResponseProviderTrait;
use Tests\Functional\App\Serialization\Helper\NormalizationHelperTrait;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer
 *
 * @group JsonRpcCallResponseNormalizer
 * @group Serialization
 */
class JsonRpcCallResponseNormalizerTest extends TestCase
{
    use JsonRpcCallResponseProviderTrait;
    use NormalizationHelperTrait;

    /** @var JsonRpcCallResponseNormalizer */
    private $normalizer;
    /** @var JsonRpcResponseNormalizer|ObjectProphecy */
    private $responseNormalizer;

    protected function setUp()
    {
        $this->responseNormalizer = $this->prophesize(JsonRpcResponseNormalizer::class);

        $this->normalizer = new JsonRpcCallResponseNormalizer(
            $this->responseNormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideValidCallResponseData
     *
     * @param JsonRpcCallResponse $callResponse
     * @param bool               $isBatch
     * @param bool               $expectNull
     */
    public function testShouldHandle(JsonRpcCallResponse $callResponse, $isBatch, $expectNull)
    {
        $expectedResponse = [];

        foreach ($callResponse->getResponseList() as $response) {
            if (true === $response->isNotification()) {
                continue;
            }
            $expectedResponse[] = $normalized = ['id' => spl_object_hash($callResponse)];
            $this->responseNormalizer->normalize($response)
                ->willReturn($normalized)
                ->shouldBeCalled();
        }

        if (false === $isBatch) {
            $expectedResponse = array_shift($expectedResponse);
        }

        $result = $this->normalizer->normalize($callResponse);

        $this->assertValidNormalization($result, $expectedResponse, $isBatch, $expectNull);
    }
}
