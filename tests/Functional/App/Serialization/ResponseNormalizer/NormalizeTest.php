<?php
namespace Tests\Functional\App\Creator\ResponseNormalizer;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer
 */
class NormalizeTest extends TestCase
{
    /** @var ResponseNormalizer */
    private $responseNormalizer;

    protected function setUp()
    {
        $this->responseNormalizer = new ResponseNormalizer();
    }

    public function testShouldReturnResult()
    {
        $expectedResult = ['expected-result'];
        $response = (new JsonRpcResponse())
            ->setResult($expectedResult)
        ;

        $result = $this->responseNormalizer->normalize($response);

        $this->assertArrayHasKey(ResponseNormalizer::KEY_RESULT, $result);
        $this->assertSame($expectedResult, $result[ResponseNormalizer::KEY_RESULT]);
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
            ResponseNormalizer::KEY_ERROR,
            $result,
            'Error not found'
        );
        $errorObject = $result[ResponseNormalizer::KEY_ERROR];

        $this->assertArrayHasKey(ResponseNormalizer::SUB_KEY_ERROR_CODE, $errorObject, 'Error code not found');
        $this->assertSame($code, $errorObject[ResponseNormalizer::SUB_KEY_ERROR_CODE], 'Error code not expected');


        $this->assertArrayHasKey(ResponseNormalizer::SUB_KEY_ERROR_MESSAGE, $errorObject, 'Error message not found');
        $this->assertSame(
            $message,
            $errorObject[ResponseNormalizer::SUB_KEY_ERROR_MESSAGE],
            'Error message not expected'
        );
    }

    public function testShouldNormalizeErrorWithData()
    {
        $data = ['error-data'];
        $response = (new JsonRpcResponse())
            ->setError(new JsonRpcException(4321, 'error-message', $data));

        $result = $this->responseNormalizer->normalize($response);

        $errorObject = $result[ResponseNormalizer::KEY_ERROR];

        $this->assertArrayHasKey(ResponseNormalizer::SUB_KEY_ERROR_DATA, $errorObject, 'Error data not found');
        $this->assertSame($data, $errorObject[ResponseNormalizer::SUB_KEY_ERROR_DATA], 'Error data not expected');
    }
}
