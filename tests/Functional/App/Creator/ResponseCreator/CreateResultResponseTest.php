<?php
namespace Tests\Functional\App\Creator\ResponseCreator;

/**
 * @covers \Yoanm\JsonRpcServer\App\Creator\ResponseCreator
 *
 * @group ResponseCreator
 */
class CreateResultResponseTest extends BaseTestCase
{
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
