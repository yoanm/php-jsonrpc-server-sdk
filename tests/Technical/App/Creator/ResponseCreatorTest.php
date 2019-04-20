<?php
namespace Tests\Technical\App\Creator;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Creator\ResponseCreator
 */
class ResponseCreatorTest extends TestCase
{
    const DEFAULT_JSONRPC = '2.0';
    const DEFAULT_ID = '1234567890';
    const DEFAULT_METHOD = 'defaultMethod';

    /** @var ResponseCreator */
    private $responseCreator;


    protected function setUp(): void
    {
        $this->responseCreator = new ResponseCreator();
    }

    /**
     * Should convert other exception to a JsonRpcInternalErrorException exception
     */
    public function testCreateErrorResponseConvertOtherExceptions()
    {
        $message = 'my-message';
        $exception = new \Exception($message);

        $response = $this->responseCreator->createErrorResponse($exception);

        $this->assertInstanceOf(JsonRpcInternalErrorException::class, $response->getError());
        $this->assertSame(
            $message,
            $response->getError()->getErrorData()[JsonRpcInternalErrorException::DATA_PREVIOUS_KEY]
        );
    }
}
