<?php
namespace Tests\Functional\App\Creator\ResponseCreator;

use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * @covers \Yoanm\JsonRpcServer\App\Creator\ResponseCreator
 *
 * @uses \Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest
 * @uses \Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse
 */
class CreateEmptyResponseTest extends BaseTestCase
{

    /**
     * Should create an instance of JsonRpcResponse
     */
    public function testShouldReturnRightInstance()
    {
        $response = $this->responseCreator->createEmptyResponse();

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
    }

    /**
     * Should bind following properties  to response :
     *  - json-rpc version from request
     *  - id from request
     */
    public function testShouldBindRequestParamToResponse()
    {
        $fromRequest = $this->createRequest();

        $response = $this->responseCreator->createEmptyResponse($fromRequest);

        $this->assertFromRequestBinding($fromRequest, $response);
    }

    /**
     * Should create a notification response from a notification request
     */
    public function testShouldCreateNotificationIfRequestIsNotification()
    {
        $fromRequest = $this->createNotificationRequest();

        $response = $this->responseCreator->createEmptyResponse($fromRequest);

        $this->assertResponseIsNotification($response);
    }
}
