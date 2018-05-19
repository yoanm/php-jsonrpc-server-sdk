<?php
namespace Yoanm\JsonRpcServer\App\Handler;

use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Dispatcher\JsonRpcServerDispatcherAwareTrait;
use Yoanm\JsonRpcServer\Domain\Event\Action as ActionEvent;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidParamsException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

/**
 * Class JsonRpcRequestHandler
 */
class JsonRpcRequestHandler
{
    use JsonRpcServerDispatcherAwareTrait;

    /** @var JsonRpcMethodResolverInterface */
    private $methodResolver;
    /** @var ResponseCreator */
    private $responseCreator;

    /**
     * @param JsonRpcMethodResolverInterface $methodResolver
     * @param ResponseCreator                $responseCreator
     */
    public function __construct(JsonRpcMethodResolverInterface $methodResolver, ResponseCreator $responseCreator)
    {
        $this->methodResolver = $methodResolver;
        $this->responseCreator = $responseCreator;
    }

    /**
     * @param JsonRpcRequest $item
     *
     * @return JsonRpcResponse
     *
     * @throws JsonRpcInvalidParamsException
     * @throws JsonRpcMethodNotFoundException
     */
    public function processJsonRpcRequest(JsonRpcRequest $jsonRpcRequest) : JsonRpcResponse
    {
        $method = $this->resolveMethod($jsonRpcRequest);

        $this->validateParamList($jsonRpcRequest, $method);

        try {
            $result = $method->apply($jsonRpcRequest->getParamList());
            $event = new ActionEvent\OnMethodSuccessEvent($result, $method, $jsonRpcRequest);
        } catch (\Exception $exception) {
            $event = new ActionEvent\OnMethodFailureEvent($exception, $method, $jsonRpcRequest);
        }

        $this->dispatchJsonRpcEvent($event::EVENT_NAME, $event);

        if ($event instanceof ActionEvent\OnMethodSuccessEvent) {
            $response = $this->responseCreator->createResultResponse($event->getResult(), $event->getJsonRpcRequest());
        } else {
            $response = $this->responseCreator->createErrorResponse(
                $event->getException(),
                $event->getJsonRpcRequest()
            );
        }

        return $response;
    }

    /**
     * @param JsonRpcRequest $jsonRpcRequest
     *
     * @return JsonRpcMethodInterface
     *
     * @throws JsonRpcMethodNotFoundException
     */
    private function resolveMethod(JsonRpcRequest $jsonRpcRequest) : JsonRpcMethodInterface
    {
        $method = $this->methodResolver->resolve($jsonRpcRequest->getMethod());

        if (!$method instanceof JsonRpcMethodInterface) {
            throw new JsonRpcMethodNotFoundException($jsonRpcRequest->getMethod());
        }

        return $method;
    }

    /**
     * @param JsonRpcRequest $jsonRpcRequest
     * @param JsonRpcMethodInterface $method
     *
     * @throws JsonRpcInvalidParamsException
     */
    private function validateParamList(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method)
    {
        $event = new ActionEvent\ValidateParamsEvent($method, $jsonRpcRequest->getParamList() ?? []);

        $this->dispatchJsonRpcEvent($event::EVENT_NAME, $event);

        if (count($event->getViolationList())) {
            throw new JsonRpcInvalidParamsException($event->getViolationList());
        }
    }
}
