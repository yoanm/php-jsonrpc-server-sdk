<?php
namespace Tests\Functional\Infra\Serialization\RawRequestSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer
 */
class DeserializeTest extends TestCase
{
    use RequestStringProviderTrait;
    use DenormalizationValidatorTrait;

    /** @var RawRequestSerializer */
    private $rawRequestSerializer;
    /** @var RequestDenormalizer|ObjectProphecy */
    private $requestDenormalizer;

    protected function setUp()
    {
        $this->requestDenormalizer = $this->prophesize(RequestDenormalizer::class);
        $this->rawRequestSerializer = new RawRequestSerializer(
            $this->requestDenormalizer->reveal()
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

        $this->requestDenormalizer->denormalize(Argument::cetera())
            ->willReturn($this->prophesize(JsonRpcRequest::class)->reveal())
            ->shouldBeCalled()
        ;

        $rawRequest = $this->rawRequestSerializer->deserialize($content);

        $this->assertSame($isBatch, $rawRequest->isBatch());

        $this->assertValidDenormalization($decodedContent, $rawRequest, $isBatch);
    }
}
