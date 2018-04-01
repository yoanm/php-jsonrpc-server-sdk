<?php
namespace Tests\Technical\App\Creator;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
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


    protected function setUp()
    {
        $this->responseCreator = new ResponseCreator();
    }

    /**
     * Should convert other exception to a JsonRpcInternalErrorException exception
     */
    public function testCreateErrorResponseConvertOtherExceptions()
    {
        $exception = new \Exception();

        $response = $this->responseCreator->createErrorResponse($exception);

        $this->assertInstanceOf(JsonRpcInternalErrorException::class, $response->getError());
        $this->assertSame(
            $exception,
            $response->getError()->getErrorData()[JsonRpcInternalErrorException::DATA_PREVIOUS_KEY]
        );
    }
}
