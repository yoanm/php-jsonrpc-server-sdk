<?php
namespace Yoanm\JsonRpcServer\Infra\Endpoint;

use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\RequestHandler;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawRequest;
use Yoanm\JsonRpcServer\Infra\RawObject\JsonRpcRawResponse;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;

/**
 * Class JsonRpcEndpoint
 */
class JsonRpcEndpoint
{
    /** @var RawRequestSerializer */
    private $rawRequestSerializer;
    /** @var RequestHandler */
    private $requestHandler;
    /** @var ResponseCreator */
    private $responseCreator;
    /** @var RawResponseSerializer */
    private $rawResponseNormalizer;

    /**
     * @param RawRequestSerializer $rawRequestSerializer
     * @param RequestHandler $requestHandler
     * @param RawResponseSerializer $rawResponseNormalizer
     * @param ResponseCreator $responseCreator
     */
    public function __construct(
        RawRequestSerializer $rawRequestSerializer,
        RequestHandler $requestHandler,
        RawResponseSerializer $rawResponseNormalizer,
        ResponseCreator $responseCreator
    ) {
        $this->rawRequestSerializer = $rawRequestSerializer;
        $this->requestHandler = $requestHandler;
        $this->rawResponseNormalizer = $rawResponseNormalizer;
        $this->responseCreator = $responseCreator;
    }

    /**
     * @param string $request
     *
     * @return string The response
     */
    public function index(string $request) : string
    {
        try {
            $rawResponse = $this->handleRawRequest(
                $this->rawRequestSerializer->deserialize($request)
            );
        } catch (JsonRpcExceptionInterface $jsonRpcException) {
            // Try to create a valid json-rpc error
            $rawResponse = $this->createRawResponseFromException($jsonRpcException);
        } catch (\Exception $exception) {
            // Try to create a valid json-rpc error anyway
            $rawResponse = $this->createRawResponseFromException(
                new JsonRpcInternalErrorException($exception)
            );
        }

        return $this->rawResponseNormalizer->serialize($rawResponse);
    }

    /**
     * @param JsonRpcRawRequest $rawRequest
     *
     * @return JsonRpcRawResponse
     */
    private function handleRawRequest(JsonRpcRawRequest $rawRequest) : JsonRpcRawResponse
    {
        $rawResponse = new JsonRpcRawResponse($rawRequest->isBatch());

        foreach ($rawRequest->getItemtList() as $item) {
            if ($item instanceof \Exception) {
                $response = $this->responseCreator->createErrorResponse($item);
            } else {
                $response = $this->requestHandler->handle($item);
            }

            $rawResponse->addResponse($response);
        }

        return $rawResponse;
    }

    /**
     * @param JsonRpcException $exception
     *
     * @return JsonRpcRawResponse
     */
    private function createRawResponseFromException(JsonRpcException $exception) : JsonRpcRawResponse
    {
        return (new JsonRpcRawResponse())->addResponse(
            $this->responseCreator->createErrorResponse($exception)
        );
    }
}
