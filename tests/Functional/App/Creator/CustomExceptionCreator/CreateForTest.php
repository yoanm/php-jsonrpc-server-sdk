<?php
namespace Tests\Functional\App\Creator\CustomExceptionCreator;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator
 *
 * @uses \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException
 * @uses \Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException
 */
class CreateForTest extends TestCase
{
    const DEFAULT_ERROR_CODE = -32001;
    const DEFAULT_ERROR_MESSAGE = 'default error message';

    /** @var CustomExceptionCreator */
    private $customExceptionCreator;

    protected function setUp()
    {
        $this->customExceptionCreator = new CustomExceptionCreator();
    }

    /**
     * Should store previous exception under specific key inside result data
     */
    public function testShouldHandlePreviousException()
    {
        $message = self::DEFAULT_ERROR_MESSAGE;
        $code = self::DEFAULT_ERROR_CODE;
        $previousException = new \Exception();

        $exception = new \Exception($message, $code, $previousException);

        $result = $this->customExceptionCreator->createFor($exception);

        $this->assertArrayHasKey(
            CustomExceptionCreator::ERROR_DATA_PREVIOUS_KEY,
            $result->getErrorData(),
            'Data array does not have required key'
        );
        $this->assertSame(
            $previousException,
            $result->getErrorData()[CustomExceptionCreator::ERROR_DATA_PREVIOUS_KEY],
            'Previous exception not found'
        );
    }

    /**
     * Should create an instance of JsonRpcException with given code and message
     */
    public function testShouldBindErrorAndMessage()
    {
        $message = self::DEFAULT_ERROR_MESSAGE;
        $code = self::DEFAULT_ERROR_CODE;

        $exception = new \Exception($message, $code);

        $result = $this->customExceptionCreator->createFor($exception);

        $this->assertInstanceOf(JsonRpcException::class, $result);
        $this->assertSame($code, $result->getErrorCode(), 'Error code mismatch');
        $this->assertSame($message, $result->getErrorMessage(), 'Error message mismatch');
    }
}
