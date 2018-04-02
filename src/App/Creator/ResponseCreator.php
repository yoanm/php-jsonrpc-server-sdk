<?php
namespace Yoanm\JsonRpcServer\App\Creator;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * Class ResponseCreator
 */
class ResponseCreator
{
    /**
     * @param JsonRpcRequest|null $fromRequest
     *
     * @return JsonRpcResponse
     */
    public function createEmptyResponse(JsonRpcRequest $fromRequest = null) : JsonRpcResponse
    {
        if (null === $fromRequest) {
            return new JsonRpcResponse();
        }

        $response = (new JsonRpcResponse($fromRequest->getJsonRpc()))
                ->setIsNotification($fromRequest->isNotification())
        ;

        if ($fromRequest->getId()) {
            $response->setId($fromRequest->getId());
        }

        return $response;
    }

    /**
     * @param mixed               $result
     * @param JsonRpcRequest|null $fromRequest
     *
     * @return JsonRpcResponse
     */
    public function createResultResponse($result, JsonRpcRequest $fromRequest = null) : JsonRpcResponse
    {
        return $this->createEmptyResponse($fromRequest)
            ->setResult($result);
    }

    /**
     * @param \Exception          $exception
     * @param JsonRpcRequest|null $fromRequest
     *
     * @return JsonRpcResponse
     */
    public function createErrorResponse(\Exception $exception, JsonRpcRequest $fromRequest = null) : JsonRpcResponse
    {
        return $this->createEmptyResponse($fromRequest)
            ->setIsNotification(false)
            ->setError(
                $exception instanceof JsonRpcExceptionInterface
                    ? $exception
                    : new JsonRpcInternalErrorException($exception)
            )
        ;
    }
}
