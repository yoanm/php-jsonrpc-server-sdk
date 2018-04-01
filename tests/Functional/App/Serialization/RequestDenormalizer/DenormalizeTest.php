<?php
namespace Tests\Functional\App\Creator\RequestDenormalizer;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer
 */
class DenormalizeTest extends TestCase
{
    /** @var RequestDenormalizer */
    private $requestDenormalizer;

    protected function setUp()
    {
        $this->requestDenormalizer = new RequestDenormalizer();
    }

    public function testShouldBindArrayProperties()
    {
        $item = [
            'json-rpc' => 'expected-json-rpc-version',
            'id' => 'expected-id',
            'method' => 'expected-method',
            'params' => ['expected-params'],
        ];

        $result = $this->requestDenormalizer->denormalize($item);

        $this->assertSame(
            $item['json-rpc'],
            $result->getJsonRpc(),
            'JSON-RPC version does not match'
        );
        $this->assertSame(
            $item['id'],
            $result->getId(),
            'Id does not match'
        );
        $this->assertSame(
            $item['method'],
            $result->getMethod(),
            'Method does not match'
        );
        $this->assertSame(
            $item['params'],
            $result->getParamList(),
            'Params does not match'
        );
    }

    public function testShouldHandleNotificationRequest()
    {
        $item = [
            'json-rpc' => 'expected-json-rpc-version',
            'method' => 'expected-method'
        ];

        $result = $this->requestDenormalizer->denormalize($item);

        $this->assertTrue(
            $result->isNotification(),
            'Result is not a notification'
        );
    }

    /**
     * Should throw a JsonRpcInvalidRequestException if "method" key is not provided
     */
    public function testShouldThrowAnExceptionIfMethodIsNotProvided()
    {
        $item = [
            'json-rpc' => 'fake-json-rpc-version',
            'id' => 'fake-id',
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);

        try {
            $this->requestDenormalizer->denormalize($item);
        } catch (JsonRpcInvalidRequestException $e) {
            // Assert error description
            $description = $e->getErrorData()[JsonRpcInvalidRequestException::DESCRIPTION_KEY];
            $this->assertContains(
                '"method" is a required key',
                $description,
                'Exception is not regarding expected field'
            );

            throw $e;
        }
    }

    /**
     * Should throw a JsonRpcInvalidRequestException if "json-rpc" key is not provided
     */
    public function testShouldThrowAnExceptionIfJsonRpcVersionIsNotProvided()
    {
        $item = [
            'method' => 'fake-method',
            'id' => 'fake-id',
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);

        try {
            $this->requestDenormalizer->denormalize($item);
        } catch (JsonRpcInvalidRequestException $e) {
            // Assert error description
            $description = $e->getErrorData()[JsonRpcInvalidRequestException::DESCRIPTION_KEY];
            $this->assertContains(
                '"json-rpc" is a required key',
                $description,
                'Exception is not regarding expected field'
            );

            throw $e;
        }
    }
}
