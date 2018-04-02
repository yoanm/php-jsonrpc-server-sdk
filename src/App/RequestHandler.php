<?php
namespace Yoanm\JsonRpcServer\App;

use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * Class RequestHandler
 */
class RequestHandler
{
    /** @var MethodManager */
    private $methodManager;
    /** @var ResponseCreator */
    private $responseCreator;

    /**
     * @param MethodManager   $methodManager
     * @param ResponseCreator $responseCreator
     */
    public function __construct(MethodManager $methodManager, ResponseCreator $responseCreator)
    {
        $this->methodManager = $methodManager;
        $this->responseCreator = $responseCreator;
    }

    /**
     * @param JsonRpcRequest $jsonRpcRequest
     *
     * @return JsonRpcResponse
     */
    public function handle(JsonRpcRequest $jsonRpcRequest) : JsonRpcResponse
    {
        try {
            return $this->responseCreator->createResultResponse(
                $this->methodManager->apply(
                    $jsonRpcRequest->getMethod(),
                    $jsonRpcRequest->getParamList()
                ),
                $jsonRpcRequest
            );
        } catch (\Exception $exception) {
            return $this->responseCreator->createErrorResponse($exception, $jsonRpcRequest);
        }
    }
}
