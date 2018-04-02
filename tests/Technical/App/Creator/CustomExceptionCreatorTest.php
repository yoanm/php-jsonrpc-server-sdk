<?php
namespace Tests\Technical\App\Creator;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator
 */
class CustomExceptionCreatorTest extends TestCase
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
     * Should return an instance of JsonRpcInternalErrorException in case of invalid JSON-RPC error code
     */
    public function testCreateForWithInvalidErrorCode()
    {
        $exception = new \Exception(self::DEFAULT_ERROR_MESSAGE, 1234);

        $result = $this->customExceptionCreator->createFor($exception);

        $this->assertInstanceOf(
            JsonRpcInternalErrorException::class,
            $result,
            'Invalid error code should result to a JsonRpcInternalErrorException exception'
        );
    }
}
