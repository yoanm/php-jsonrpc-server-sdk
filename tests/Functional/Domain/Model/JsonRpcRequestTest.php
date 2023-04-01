<?php
namespace Tests\Functional\Domain\Model;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * @covers \Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest
 *
 * @group Models
 */
class JsonRpcRequestTest extends TestCase
{

    use ProphecyTrait;
    use IdProviderTrait;

    public function testShouldHaveMethodAndJsonRpcVersion()
    {
        $jsonRpc = 'jsonRpc';
        $method = 'my-method';

        $request = $this->createRequest($jsonRpc, $method);

        $this->assertSame($jsonRpc, $request->getJsonRpc());
        $this->assertSame($method, $request->getMethod());
    }

    public function testShouldBeANotificationIfNoIdGiven()
    {
        $request = $this->createRequest();

        $this->assertTrue($request->isNotification());
    }

    /**
     * @dataProvider provideValidIdData
     * @param mixed $id
     */
    public function testShouldHandleIdWhenTypeIs($id)
    {
        $request = $this->createRequest()
            ->setId($id);

        $this->assertSame($id, $request->getId());
    }

    public function testShouldHandleAParamList()
    {
        $paramList = ['param-list'];

        $request = $this->createRequest()
            ->setParamList($paramList);

        $this->assertSame($paramList, $request->getParamList());
    }

    /**
     * @param string $jsonRpc
     * @param string $method
     *
     * @return JsonRpcRequest
     */
    private function createRequest(string $jsonRpc = 'json-rpc-version', string $method = 'default-method')
    {
        return new JsonRpcRequest($jsonRpc, $method);
    }
}
