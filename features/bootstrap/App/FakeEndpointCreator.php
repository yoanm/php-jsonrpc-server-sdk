<?php
namespace Tests\Functional\BehatContext\App;

use Prophecy\Argument;
use Tests\Functional\BehatContext\App\Method\AbstractMethod;
use Tests\Functional\BehatContext\App\Method\BasicMethod;
use Tests\Functional\BehatContext\App\Method\BasicMethodWithRequiredParams;
use Tests\Functional\BehatContext\App\Method\MethodThatThrowExceptionDuringExecution;
use Tests\Functional\BehatContext\App\Method\MethodThatThrowJsonRpcExceptionDuringExecution;
use Tests\Functional\BehatContext\App\Method\MethodWithParamsValidationError;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcRequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Event\Action\ValidateParamsEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

class FakeEndpointCreator
{
    /**@return JsonRpcEndpoint
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

        if ($dispatcher) {
            /** Add basic params validation */
            $dispatcher->addJsonRpcListener(
                ValidateParamsEvent::EVENT_NAME,
                function (ValidateParamsEvent $event) {
                    $method = $event->getMethod();
                    if (!$method instanceof AbstractMethod) {
                        return;
                    }
                    $extraViolationList = $method->validateParams($event->getParamList());
                    if (count($extraViolationList)) {
                        // Append violations to current list
                        $event->setViolationList(
                            array_merge(
                                $event->getViolationList(),
                                $extraViolationList
                            )
                        );
                    }
                }
            );

            $endpoint->setJsonRpcServerDispatcher($dispatcher);
            $requestHandler->setJsonRpcServerDispatcher($dispatcher);
            $exceptionHandler->setJsonRpcServerDispatcher($dispatcher);
        }

        return $endpoint;
    }
}
