<?php
namespace Tests\Functional\App\Handler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Handler\ExceptionHandler
 *
 * @group ExceptionHandler
 */
class ExceptionHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ExceptionHandler */
    private $exceptionHandler;
    /** @var ResponseCreator */
    private $responseCreator;

    protected function setUp(): void
    {
        $this->responseCreator = $this->prophesize(ResponseCreator::class);

        $this->exceptionHandler = new ExceptionHandler(
            $this->responseCreator->reveal()
        );
    }

    public function testShouldRelyOnResponseCreator()
    {
        /** @var ObjectProphecy|\Exception $fakeException */
        $fakeException = $this->prophesize(\Exception::class);
        /** @var JsonRpcResponse|ObjectProphecy $response */
        $response = $this->prophesize(JsonRpcResponse::class);

        $this->responseCreator->createErrorResponse($fakeException->reveal(), null)
            ->willReturn($response->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $response->reveal(),
            $this->exceptionHandler->getJsonRpcResponseFromException($fakeException->reveal())
        );
    }

    public function testShouldRelyOnResponseCreatorAndPassRequest()
    {
        /** @var JsonRpcRequest|ObjectProphecy $fakeRequest */
        $fakeRequest = $this->prophesize(JsonRpcRequest::class);
        /** @var ObjectProphecy|\Exception $fakeException */
        $fakeException = $this->prophesize(\Exception::class);
        /** @var JsonRpcResponse|ObjectProphecy $response */
        $response = $this->prophesize(JsonRpcResponse::class);

        $this->responseCreator->createErrorResponse($fakeException->reveal(), $fakeRequest->reveal())
            ->willReturn($response->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $response->reveal(),
            $this->exceptionHandler->getJsonRpcResponseFromException($fakeException->reveal(), $fakeRequest->reveal())
        );
    }
}
