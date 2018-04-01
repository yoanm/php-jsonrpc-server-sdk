<?php
namespace Tests\Functional\App\Creator\ResponseCreator;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

class BaseTestCase extends TestCase
{
    const DEFAULT_JSONRPC = '2.0';
    const DEFAULT_ID = '1234567890';
    const DEFAULT_METHOD = 'defaultMethod';

    /** @var ResponseCreator */
    protected $responseCreator;


    protected function setUp()
    {
        $this->responseCreator = new ResponseCreator();
    }

    /**
     * @param string          $method
     * @param string          $jsonRpc
     * @param string|int|null $id
     *
     * @return JsonRpcRequest
     */
    protected function createRequest(
        string $method = self::DEFAULT_METHOD,
        string $jsonRpc = self::DEFAULT_JSONRPC,
        $id = self::DEFAULT_ID
    ) {
        $fromRequest = (new JsonRpcRequest($jsonRpc, $method))
            ->setId($id);

        return $fromRequest;
    }

    /**
     * @return JsonRpcRequest
     */
    protected function createNotificationRequest(
        string $method = self::DEFAULT_METHOD,
        string $jsonRpc = self::DEFAULT_JSONRPC
    ) {
        $fromRequest = new JsonRpcRequest($jsonRpc, $method);

        // Check that it's really a notification
        $this->assertTrue($fromRequest->isNotification(), 'FromRequest must be a notification !');

        return $fromRequest;
    }

    /**
     * @param JsonRpcResponse $response
     */
    protected function assertResponseIsNotification(JsonRpcResponse $response)
    {
        $this->assertTrue($response->isNotification(), 'Response must be a notification');
    }

    /**
     * @param JsonRpcRequest  $fromRequest
     * @param JsonRpcResponse $result
     */
    protected function assertFromRequestBinding(JsonRpcRequest $fromRequest, JsonRpcResponse $result)
    {
        $this->assertSame($fromRequest->getJsonRpc(), $result->getJsonRpc());
        $this->assertSame($fromRequest->getId(), $result->getId());
    }
}
