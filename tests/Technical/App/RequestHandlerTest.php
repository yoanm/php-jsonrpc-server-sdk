<?php
namespace Tests\Technical\App;

use PHPUnit\Framework\TestCase;
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
class RequestHandlerTest extends TestCase
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

    public function testHandleShouldManageExceptionFromMethodExecution()
    {
        $request = new JsonRpcRequest('json-rpc-version', 'method');
        $exception = $this->prophesize(\Exception::class);

        $this->methodManager->apply(Argument::cetera())
            ->willThrow($exception->reveal());
        $this->responseCreator->createErrorResponse($exception->reveal(), Argument::cetera())
            ->willReturn($this->prophesize(JsonRpcResponse::class)->reveal());

        $this->assertInstanceOf(
            JsonRpcResponse::class,
            $this->requestHandler->handle($request)
        );
    }
}
