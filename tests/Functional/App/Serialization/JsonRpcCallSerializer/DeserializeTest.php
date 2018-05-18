<?php
namespace Tests\Functional\App\Serialization\JsonRpcCallSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

/**
 * @covers Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer
 */
class DeserializeTest extends TestCase
{
    use RequestStringProviderTrait;
    use DenormalizationValidatorTrait;

    /** @var JsonRpcCallSerializer */
    private $jsonRpcCallSerializer;
    /** @var JsonRpcCallDenormalizer */
    private $callDenormalizer;
    /** @var JsonRpcCallResponseNormalizer */
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
     * @dataProvider provideValidRequestStringData
     *
     * @param string $content
     */
    public function testShouldHandle($content, $isNotification, $isBatch)
    {
        $decodedContent = json_decode($content, true);
        $rawRequest = $this->prophesize(JsonRpcCall::class);

        $this->callDenormalizer->denormalize(Argument::cetera())
            ->willReturn($rawRequest->reveal())
            ->shouldBeCalled()
        ;

        $this->assertSame($rawRequest->reveal(), $this->jsonRpcCallSerializer->deserialize($content));

        //$this->assertValidDenormalization($decodedContent, $rawRequest, $isBatch);
    }
}
