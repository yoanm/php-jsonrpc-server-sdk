<?php
namespace Tests\Functional\BehatContext\Helper;

use DemoApp\Method\AbstractMethod;
use DemoApp\Method\BasicMethod;
use DemoApp\Method\BasicMethodWithRequiredParams;
use DemoApp\Method\MethodThatThrowExceptionDuringExecution;
use DemoApp\Method\MethodThatThrowJsonRpcExceptionDuringExecution;
use DemoApp\Method\MethodWithParamsValidationError;
use DemoApp\Resolver\BehatMethodResolver;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcRequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodParamsValidatorInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

class FakeEndpointCreator
{
    /**
     * @return JsonRpcEndpoint
     */
    public function create(JsonRpcServerDispatcherInterface $dispatcher = null) : JsonRpcEndpoint
    {
        /** @var AbstractMethod[] $methodList */
        $methodList = [
            'basic-method' => new BasicMethod(),
            'basic-method-with-params' => new BasicMethodWithRequiredParams(),
            'method-that-throw-params-validation-exception' => new MethodWithParamsValidationError(),
            'method-that-throw-an-exception-during-execution' => new MethodThatThrowExceptionDuringExecution(),
            'method-that-throw-a-custom-jsonrpc-exception-during-execution' => new MethodThatThrowJsonRpcExceptionDuringExecution(),
        ];

        $methodResolver = new BehatMethodResolver();

        foreach ($methodList as $methodName => $method) {
            $methodResolver->addMethod($method, $methodName);
        }

        $jsonRpcSerializer = new JsonRpcCallSerializer(
            new JsonRpcCallDenormalizer(
                new JsonRpcRequestDenormalizer()
            ),
            new JsonRpcCallResponseNormalizer(
                new JsonRpcResponseNormalizer()
            )
        );
        $responseCreator = new ResponseCreator();
        $requestHandler = new JsonRpcRequestHandler($methodResolver, $responseCreator);
        $exceptionHandler = new ExceptionHandler($responseCreator);
        $endpoint = new JsonRpcEndpoint($jsonRpcSerializer, $requestHandler, $exceptionHandler);
        $requestHandler->setMethodParamsValidator(
            new class implements JsonRpcMethodParamsValidatorInterface
            {
                public function validate(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method) : array
                {
                    if (!$method instanceof AbstractMethod) {
                        return [];
                    }

                    return $method->validateParams($jsonRpcRequest->getParamList());
                }
            }
        );

        if ($dispatcher) {
            $endpoint->setJsonRpcServerDispatcher($dispatcher);
            $requestHandler->setJsonRpcServerDispatcher($dispatcher);
            $exceptionHandler->setJsonRpcServerDispatcher($dispatcher);
        }

        return $endpoint;
    }
}
