<?php
namespace Tests\Functional\App\Serialization;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\Functional\App\Serialization\Helper\DenormalizationValidatorTrait;
use Tests\Functional\App\Serialization\Helper\RequestStringProviderTrait;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcRequestDenormalizer;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer
 *
 * @group JsonRpcCallDenormalizer
 * @group Serialization
 */
class JsonRpcCallDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    use DenormalizationValidatorTrait;
    use RequestStringProviderTrait;

    /** @var JsonRpcCallDenormalizer */
    private $denormalizer;
    /** @var JsonRpcRequestDenormalizer|ObjectProphecy */
    private $requestDenormalizer;

    protected function setUp(): void
    {
        $this->requestDenormalizer = $this->prophesize(JsonRpcRequestDenormalizer::class);

        $this->denormalizer = new JsonRpcCallDenormalizer(
            $this->requestDenormalizer->reveal()
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
        $decodedContent = json_decode($content, true);
        /** @var JsonRpcRequest|ObjectProphecy $request */
        $request = $this->prophesize(JsonRpcRequest::class);

        $this->requestDenormalizer->denormalize(Argument::cetera())
            ->willReturn($request->reveal())
            ->shouldBeCalled()
        ;

        $result = $this->denormalizer->denormalize($decodedContent);

        $this->assertValidDenormalization($decodedContent, $result, $isBatch);
    }

    public function testShouldPropagateRequestDenormalizationExceptionIfNotABatchRequest()
    {
        $item = [
            'jsonrpc' => 'expected-json-rpc-version',
            'id' => 'expected-id',
            'method' => 'expected-method',
            'params' => ['expected-params'],
        ];
        $exception = new \Exception('my-message');

        $this->requestDenormalizer->denormalize($item)
            ->willThrow($exception)
            ->shouldBeCalled()
        ;

        $this->expectExceptionObject($exception);

        $this->denormalizer->denormalize($item);
    }

    public function testShouldGracefullyHandleRequestDenormalizationExceptionIfABatchRequest()
    {
        $item = [[
            'jsonrpc' => 'expected-json-rpc-version',
            'method' => 'expected-method'
        ]];
        $exception = new \Exception('my-message');

        $this->requestDenormalizer->denormalize($item[0])
            ->willThrow($exception)
            ->shouldBeCalled()
        ;

        $result = $this->denormalizer->denormalize($item);

        $this->assertValidDenormalization($item, $result, true);
        $this->assertSame([$exception], $result->getItemList());
    }
}
