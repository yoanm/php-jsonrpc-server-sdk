<?php
namespace Tests\Functional\App\Creator\ResponseCreator;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Creator\ResponseCreator
 */
class CreateResultResponseTest extends BaseTestCase
{
    /**
     * Should create an instance of JsonRpcResponse
     */
    public function testShouldReturnRightInstance()
    {
        $response = $this->responseCreator->createResultResponse(['my-result']);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
    }

    /**
     * Should handle result
     */
    public function testShouldBindResult()
    {
        $result = ['my-result'];

        $response = $this->responseCreator->createResultResponse($result);

        $this->assertSame($result, $response->getResult());
    }

    /**
     * Should bind result and also following properties  to response :
     *  - json-rpc version from request
     *  - id from request
     */
    public function testShouldBindResultAndRequestParamToResponse()
    {
        $result = ['my-result'];

        $fromRequest = $this->createRequest();

        $response = $this->responseCreator->createResultResponse($result, $fromRequest);

        $this->assertSame($result, $response->getResult());
        $this->assertFromRequestBinding($fromRequest, $response);
    }
}
