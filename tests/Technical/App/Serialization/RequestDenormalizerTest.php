<?php
namespace Tests\Technical\App\Creator;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer
 */
class RequestDenormalizerTest extends TestCase
{
    /** @var RequestDenormalizer */
    private $requestDenormalizer;

    protected function setUp()
    {
        $this->requestDenormalizer = new RequestDenormalizer();
    }

    /**
     * @dataProvider integerRequestIdProvider
     * @param mixed $requestId
     */
    public function testDenormalizeShouldCastIdToIntWhenIdIs($requestId)
    {
        $item = [
            'json-rpc' => 'fake-json-rpc-version',
            'method' => 'fake-method',
            'id' => $requestId,
        ];

        $result = $this->requestDenormalizer->denormalize($item);

        $this->assertSame((int) $result->getId(), $result->getId());
    }

    public function integerRequestIdProvider()
    {
        return [
            'real integer' => [
                'requestId' => 321,
            ],
            'integer stored as string' => [
                'requestId' => '321',
            ],
        ];
    }

    /**
     * @dataProvider invalidParamListProvider
     *
     * Should throw a JsonRpcInvalidRequestException if params are not valid
     */
    public function testDenormalizeShouldThrowAnExceptionWhenParamsIs($paramList)
    {
        $item = [
            'json-rpc' => 'fake-json-rpc-version',
            'method' => 'fake-method',
            'id' => 'fake-id',
            'params' => $paramList
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);

        try {
            $this->requestDenormalizer->denormalize($item);
        } catch (JsonRpcInvalidRequestException $e) {
            // Assert error description
            $description = $e->getErrorData()[JsonRpcInvalidRequestException::DATA_DESCRIPTION_KEY];
            $this->assertContains(
                'Parameter list must be an array',
                $description,
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
