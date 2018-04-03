<?php
namespace Tests\Functional\App\RequestHandler;

use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Json;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\App\RequestHandler;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\RequestHandler
 */
class HandleTest extends TestCase
{
    /** @var RequestHandler */
    private $requestHandler;
    /** @var MethodManager|ObjectProphecy */
    private $methodManager;
    /** @var ResponseCreator|ObjectProphecy */
    private $responseCreator;

    protected function setUp()
    {
        $this->methodManager = $this->prophesize(MethodManager::class);
        $this->responseCreator = $this->prophesize(ResponseCreator::class);

        $this->requestHandler = new RequestHandler(
            $this->methodManager->reveal(),
            $this->responseCreator->reveal()
        );
    }

    public function testShouldReturnAResponse()
    {
        $request = new JsonRpcRequest('json-rpc-version', 'method');

        $this->methodManager->apply(Argument::cetera())
            ->willReturn(['method-result']);
        $this->responseCreator->createResultResponse(Argument::cetera())
            ->willReturn($this->prophesize(JsonRpcResponse::class)->reveal());

        $this->assertInstanceOf(
            JsonRpcResponse::class,
            $this->requestHandler->handle($request)
        );
    }
}
