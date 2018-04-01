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
     * @dataProvider provideValidRequestString
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
