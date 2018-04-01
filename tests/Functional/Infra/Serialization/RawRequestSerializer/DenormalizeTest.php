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
class DenormalizeTest extends TestCase
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

        $rawRequest = $this->rawRequestSerializer->denormalize($decodedContent);

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

    public function testShouldGracefullyHandleRequestDernormalizationException()
    {
        $decodedContent = [
            'json-rpc' => '2.0',
            'method' => 'method'
        ];
        $exception = $this->prophesize(\Exception::class);

        $this->requestDenormalizer->denormalize(Argument::cetera())
            ->willThrow($exception->reveal())
            ->shouldBeCalled()
        ;

        $rawRequest = $this->rawRequestSerializer->denormalize($decodedContent);

        $this->assertSame(
            $exception->reveal(),
            $rawRequest->getItemtList()[0]
        );
    }

    public function testShouldGracefullyHandleRequestDernormalizationExceptionEvenForBatch()
    {
        $decodedContent = [
            [
                'json-rpc' => '2.0',
                'method' => 'valid-method'
            ],
            [
                'json-rpc' => '2.0',
                'method' => 'invalid-method'
            ],
            [
                'json-rpc' => '2.0',
                'method' => 'valid-method-2'
            ],
        ];
        $firstRequest = $this->prophesize(JsonRpcRequest::class);
        $lastRequest = $this->prophesize(JsonRpcRequest::class);
        $exception = $this->prophesize(\Exception::class);

        $this->requestDenormalizer->denormalize($decodedContent[0])
            ->willReturn($firstRequest->reveal())
            ->shouldBeCalled()
        ;
        $this->requestDenormalizer->denormalize($decodedContent[1])
            ->willThrow($exception->reveal())
            ->shouldBeCalled()
        ;
        $this->requestDenormalizer->denormalize($decodedContent[2])
            ->willReturn($lastRequest->reveal())
            ->shouldBeCalled()
        ;

        $rawRequest = $this->rawRequestSerializer->denormalize($decodedContent);

        $this->assertSame($firstRequest->reveal(), $rawRequest->getItemtList()[0]);
        $this->assertSame($exception->reveal(), $rawRequest->getItemtList()[1]);
        $this->assertSame($lastRequest->reveal(), $rawRequest->getItemtList()[2]);
    }
}