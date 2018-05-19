<?php
namespace Tests\Functional\Domain\Model;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse
 *
 * @group Models
 */
class JsonRpcResponseTest extends TestCase
{
    use IdProviderTrait;

    public function testShouldHaveAJsonRpcVersion()
    {
        $jsonRpc = 'jsonRpc';

        $response = $this->createResponse($jsonRpc);

        $this->assertSame($jsonRpc, $response->getJsonRpc());
    }

    public function testShouldManageNotificationResponse()
    {
        $response = $this->createResponse()->setIsNotification(true);

        $this->assertTrue($response->isNotification());
    }

    /**
     * @dataProvider provideValidIdData
     * @param mixed $id
     */
    public function testShouldHandleIdWhenTypeIs($id)
    {
        $request = $this->createResponse()
            ->setId($id);

        $this->assertSame($id, $request->getId());
    }

    public function testShouldHandleAResult()
    {
        $result = ['result'];
        $request = $this->createResponse()
            ->setResult($result);

        $this->assertSame($result, $request->getResult());
    }

    public function testShouldHandleAnError()
    {
        $error = $this->prophesize(JsonRpcExceptionInterface::class);
        $request = $this->createResponse()
            ->setError($error->reveal());

        $this->assertSame($error->reveal(), $request->getError());
    }

    /**
     * @param string $jsonRpc
     * @param string $method
     *
     * @return JsonRpcResponse
     */
    private function createResponse(string $jsonRpc = 'json-rpc-version')
    {
        return new JsonRpcResponse($jsonRpc);
    }
}
