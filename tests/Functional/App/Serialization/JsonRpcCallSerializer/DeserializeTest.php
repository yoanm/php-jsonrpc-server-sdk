<?php
namespace Tests\Functional\App\Serialization\JsonRpcCallSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\Functional\App\Serialization\Helper\RequestStringProviderTrait;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer
 *
 * @group JsonRpcCallSerializer
 * @group Serialization
 */
class DeserializeTest extends TestCase
{
    use ProphecyTrait;

    use RequestStringProviderTrait;

    /** @var JsonRpcCallSerializer */
    private $jsonRpcCallSerializer;
    /** @var JsonRpcCallDenormalizer|ObjectProphecy */
    private $callDenormalizer;
    /** @var JsonRpcCallResponseNormalizer|ObjectProphecy */
    private $callResponseNormalizer;

    protected function setUp(): void
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
     * @param bool   $isNotification
     * @param bool   $isBatch
     */
    public function testShouldHandle($content, $isNotification, $isBatch)
    {
        $rawRequest = $this->prophesize(JsonRpcCall::class);

        $this->callDenormalizer->denormalize(Argument::cetera())
            ->willReturn($rawRequest->reveal())
            ->shouldBeCalled()
        ;

        $this->assertSame($rawRequest->reveal(), $this->jsonRpcCallSerializer->deserialize($content));
    }
}
