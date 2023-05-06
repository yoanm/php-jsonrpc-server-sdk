<?php
namespace Tests\Functional\App\Creator\ResponseCreator;

use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

/**
 * @covers \Yoanm\JsonRpcServer\App\Creator\ResponseCreator
 *
 * @group ResponseCreator
 */
class CreateResultResponseTest extends BaseTestCase
{
    use ProphecyTrait;

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

    /**
     * Bug fix: https://github.com/yoanm/php-jsonrpc-server-sdk/issues/94
     */
    public function testShouldConvertRequestWithZeroIdToResponseWithZeroId() {
        $fromRequest = $this->createRequest(self::DEFAULT_METHOD, self::DEFAULT_JSONRPC, 0);

        $response = $this->responseCreator->createEmptyResponse($fromRequest);

        $this->assertSame(0, $response->getId());
    }
}
