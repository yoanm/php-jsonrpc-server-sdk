<?php
namespace Tests\Functional\App\Serialization\JsonRpcCallSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer
 *
 * @group JsonRpcCallSerializer
 */
class NormalizeTest extends TestCase
{
    use JsonRpcCallResponseProviderTrait;

    /** @var JsonRpcCallSerializer */
    private $jsonRpcCallSerializer;
    /** @var JsonRpcCallDenormalizer|ObjectProphecy */
    private $callDenormalizer;
    /** @var JsonRpcCallResponseNormalizer|ObjectProphecy */
    private $callResponseNormalizer;

    protected function setUp()
    {
        $this->callDenormalizer = $this->prophesize(JsonRpcCallDenormalizer::class);
        $this->callResponseNormalizer = $this->prophesize(JsonRpcCallResponseNormalizer::class);
        $this->jsonRpcCallSerializer = new JsonRpcCallSerializer(
            $this->callDenormalizer->reveal(),
            $this->callResponseNormalizer->reveal()
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
        $normalizedResponse = ['id' => spl_object_hash($callResponse)];
        $this->callResponseNormalizer->normalize($callResponse)
            ->willReturn($normalizedResponse)
            ->shouldBeCalled();

        $this->assertSame(
            $normalizedResponse,
            $this->jsonRpcCallSerializer->normalize($callResponse)
        );
    }
}
