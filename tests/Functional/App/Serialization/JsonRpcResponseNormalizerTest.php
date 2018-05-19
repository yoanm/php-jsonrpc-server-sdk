<?php
namespace Tests\Functional\App\Serialization;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer
 *
 * @group JsonRpcResponseNormalizer
 * @group Serialization
 */
class JsonRpcResponseNormalizerTest extends TestCase
{
    const EXPECTED_KEY_JSONRPC_VERSION = 'jsonrpc';
    const EXPECTED_KEY_ID = 'id';
    const EXPECTED_KEY_RESULT = 'result';
    const EXPECTED_KEY_ERROR = 'error';
    const EXPECTED_SUB_KEY_ERROR_CODE = 'code';
    const EXPECTED_SUB_KEY_ERROR_MESSAGE = 'message';
    const EXPECTED_SUB_KEY_ERROR_DATA = 'data';

    /** @var JsonRpcResponseNormalizer */
    private $responseNormalizer;

    protected function setUp()
    {
        $this->responseNormalizer = new JsonRpcResponseNormalizer();
    }

    public function testShouldHaveTheGivenJsonRpcVersion()
    {
        $jsonRpc = 'json-rpc-version';

        $response = new JsonRpcResponse($jsonRpc);

        $result = $this->responseNormalizer->normalize($response);

        $this->assertArrayHasKey(self::EXPECTED_KEY_JSONRPC_VERSION, $result);
        $this->assertSame($jsonRpc, $result[self::EXPECTED_KEY_JSONRPC_VERSION]);
    }

    public function testShouldHaveTheGivenId()
    {
        $id = 'my-id';

        $response = (new JsonRpcResponse())
            ->setId($id);

        $result = $this->responseNormalizer->normalize($response);

        $this->assertArrayHasKey(self::EXPECTED_KEY_ID, $result);
        $this->assertSame($id, $result[self::EXPECTED_KEY_ID]);
    }

    public function testShouldReturnResult()
    {
        $expectedResult = ['expected-result'];
        $response = (new JsonRpcResponse())
            ->setResult($expectedResult)
        ;

        $result = $this->responseNormalizer->normalize($response);

        $this->assertArrayHasKey(self::EXPECTED_KEY_RESULT, $result);
        $this->assertSame($expectedResult, $result[self::EXPECTED_KEY_RESULT]);
    }

    public function testShouldReturnNullForANotification()
    {
        $response = (new JsonRpcResponse())
            ->setIsNotification(true)
        ;

        $this->assertNull(
            $this->responseNormalizer->normalize($response),
            'A notification should be normalized to null'
        );
    }

    public function testShouldNormalizeError()
    {
        $code = 4321;
        $message = 'error-message';
        $response = (new JsonRpcResponse())
            ->setError(new JsonRpcException($code, $message));

        $result = $this->responseNormalizer->normalize($response);

        $this->assertArrayHasKey(
            self::EXPECTED_KEY_ERROR,
            $result,
            'Error not found'
        );
        $errorObject = $result[self::EXPECTED_KEY_ERROR];

        $this->assertArrayHasKey(self::EXPECTED_SUB_KEY_ERROR_CODE, $errorObject, 'Error code not found');
        $this->assertSame($code, $errorObject[self::EXPECTED_SUB_KEY_ERROR_CODE], 'Error code not expected');


        $this->assertArrayHasKey(self::EXPECTED_SUB_KEY_ERROR_MESSAGE, $errorObject, 'Error message not found');
        $this->assertSame(
            $message,
            $errorObject[self::EXPECTED_SUB_KEY_ERROR_MESSAGE],
            'Error message not expected'
        );
    }

    public function testShouldNotNormalizeErrorForANotification()
    {
        $code = 4321;
        $message = 'error-message';
        $response = (new JsonRpcResponse())
            ->setIsNotification(true)
            ->setError(new JsonRpcException($code, $message));

        $this->assertNull($this->responseNormalizer->normalize($response));
    }

    public function testShouldNormalizeErrorWithData()
    {
        $data = ['error-data'];
        $response = (new JsonRpcResponse())
            ->setError(new JsonRpcException(4321, 'error-message', $data));

        $result = $this->responseNormalizer->normalize($response);

        $errorObject = $result[self::EXPECTED_KEY_ERROR];

        $this->assertArrayHasKey(self::EXPECTED_SUB_KEY_ERROR_DATA, $errorObject, 'Error data not found');
        $this->assertSame($data, $errorObject[self::EXPECTED_SUB_KEY_ERROR_DATA], 'Error data not expected');
    }
}
