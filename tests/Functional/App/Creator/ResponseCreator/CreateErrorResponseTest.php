<?php
namespace Tests\Functional\App\Creator\ResponseCreator;

use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Creator\ResponseCreator
 *
 * @group ResponseCreator
 */
class CreateErrorResponseTest extends BaseTestCase
{
    use ProphecyTrait;

    /**
     * Should handle error
     */
    public function testShouldBindException()
    {
        $exception = $this->prophesize(JsonRpcException::class);

        $response = $this->responseCreator->createErrorResponse($exception->reveal());

        $this->assertSame($exception->reveal(), $response->getError());
    }

    /**
     * Should bind exception and also following properties  to response :
     *  - json-rpc version from request
     *  - id from request
     */
    public function testShouldBindExceptionAndRequestParamToResponse()
    {
        /** @var ObjectProphecy|JsonRpcException $exception */
        $exception = $this->prophesize(JsonRpcException::class);

        $fromRequest = $this->createRequest();

        $response = $this->responseCreator->createErrorResponse($exception->reveal(), $fromRequest);

        $this->assertSame($exception->reveal(), $response->getError());
        $this->assertFromRequestBinding($fromRequest, $response);
    }
}
