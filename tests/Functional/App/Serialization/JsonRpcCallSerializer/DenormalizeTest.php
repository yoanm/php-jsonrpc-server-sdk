<?php
namespace Tests\Functional\App\Serialization\JsonRpcCallSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer
 */
class DenormalizeTest extends TestCase
{
    use RequestStringProviderTrait;
    use DenormalizationValidatorTrait;

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
     * @dataProvider provideValidRequestStringData
     *
     * @param string $content
     */
    public function testShouldHandle($content, $isNotification, $isBatch)
    {
        $decodedContent = json_decode($content, true);
        $jsonRpcCall = $this->prophesize(JsonRpcCall::class);

        $this->callDenormalizer->denormalize(Argument::cetera())
            ->willReturn($jsonRpcCall->reveal())
            ->shouldBeCalled()
        ;

        $this->assertSame($jsonRpcCall->reveal(), $this->jsonRpcCallSerializer->denormalize($decodedContent));

     //   $this->assertValidDenormalization($decodedContent, $jsonRpcCall, $isBatch);
    }

    /*
    public function testShouldGracefullyHandleRequestDernormalizationException()
    {
        $decodedContent = [
            'jsonrpc' => '2.0',
            'method' => 'method'
        ];
        $exception = $this->prophesize(\Exception::class);

        $this->callDenormalizer->denormalize(Argument::cetera())
            ->willThrow($exception->reveal())
            ->shouldBeCalled()
        ;

        $jsonRpcCall = $this->jsonRpcCallSerializer->denormalize($decodedContent);

        $this->assertSame(
            $exception->reveal(),
            $jsonRpcCall->getItemList()[0]
        );
    }
    */

    /*
    public function testShouldGracefullyHandleRequestDernormalizationExceptionEvenForBatch()
    {
        $decodedContent = [
            [
                'jsonrpc' => '2.0',
                'method' => 'valid-method'
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'invalid-method'
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'valid-method-2'
            ],
        ];
        $firstRequest = $this->prophesize(JsonRpcCall::class);
        $lastRequest = $this->prophesize(JsonRpcCall::class);
        $exception = $this->prophesize(\Exception::class);

        $this->callDenormalizer->denormalize($decodedContent[0])
            ->willReturn($firstRequest->reveal())
            ->shouldBeCalled()
        ;
        $this->callDenormalizer->denormalize($decodedContent[1])
            ->willThrow($exception->reveal())
            ->shouldBeCalled()
        ;
        $this->callDenormalizer->denormalize($decodedContent[2])
            ->willReturn($lastRequest->reveal())
            ->shouldBeCalled()
        ;

        $jsonRpcCall = $this->jsonRpcCallSerializer->denormalize($decodedContent);

        $this->assertSame($firstRequest->reveal(), $jsonRpcCall->getItemList()[0]);
        $this->assertSame($exception->reveal(), $jsonRpcCall->getItemList()[1]);
        $this->assertSame($lastRequest->reveal(), $jsonRpcCall->getItemList()[2]);
    }
    */
}
