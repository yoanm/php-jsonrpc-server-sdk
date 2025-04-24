<?php
namespace Tests\Technical\App\Serialization;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcRequestDenormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcRequestDenormalizer
 *
 * @group JsonRpcRequestDenormalizer
 * @group Serialization
 */
class JsonRpcRequestDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    /** @var JsonRpcRequestDenormalizer */
    private $requestDenormalizer;

    protected function setUp(): void
    {
        $this->requestDenormalizer = new JsonRpcRequestDenormalizer();
    }

    /**
     * @dataProvider invalidParamListProvider
     *
     * Should throw a JsonRpcInvalidRequestException if params are not valid
     *
     * @param array $paramList
     */
    public function testDenormalizeShouldThrowAnExceptionWhenParamsIs($paramList)
    {
        $item = [
            'jsonrpc' => 'fake-json-rpc-version',
            'method' => 'fake-method',
            'id' => 'fake-id',
            'params' => $paramList
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);

        try {
            $this->requestDenormalizer->denormalize($item);
        } catch (JsonRpcInvalidRequestException $e) {
            // Assert error description
            $this->assertStringContainsString(
                'Parameter list must be an array',
                $e->getDescription(),
                'Exception description is not the expected one'
            );

            throw $e;
        }
    }

    public function invalidParamListProvider()
    {
        return [
            'boolean value' => [
                'paramList' => true,
            ],
            'string value' => [
                'paramList' => 'invalid-params',
            ],
            'object value' => [
                'paramList' => new \Exception(),
            ],
        ];
    }
}
