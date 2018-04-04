<?php
namespace Tests\Functional\BehatContext\App;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\App\RequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;
use Yoanm\SymfonyJsonRpcServer\App\Resolver\DefaultServiceNameResolver;
use Yoanm\SymfonyJsonRpcServer\Infra\Resolver\ContainerJsonRpcMethodResolver;

class FakeEndpointCreator
{
    /**@return JsonRpcEndpoint
     */
    public function create() : JsonRpcEndpoint
    {
        $prophet = new Prophet();

        $methodResolver = $prophet->prophesize(MethodResolverInterface::class);
        // Return null by default
        $methodResolver->resolve(Argument::any())->willReturn(null);

        $this->addMethod($methodResolver, 'basic-method', $this->getBasicMethod($prophet));

        $this->addMethod(
            $methodResolver,
            'basic-method-with-params',
            $this->getMethodWithRequiredParams($prophet)
        );
        $this->addMethod(
            $methodResolver,
            'method-that-throw-params-validation-exception',
            $this->getParamsValidationExceptionMethod($prophet)
        );
        $this->addMethod(
            $methodResolver,
            'method-that-throw-params-validation-exception',
            $this->getParamsValidationExceptionMethod($prophet)
        );
        $this->addMethod(
            $methodResolver,
            'method-that-throw-an-exception-during-execution',
            $this->getExecutionExceptionMethod($prophet)
        );

        $this->addMethod(
            $methodResolver,
            'method-that-throw-a-custom-jsonrpc-exception-during-execution',
            $this->getCustomExecutionExceptionMethod($prophet)
        );

        $responseCreator = new ResponseCreator();
        return new JsonRpcEndpoint(
            new RawRequestSerializer(
                new RequestDenormalizer()
            ),
            new RequestHandler(
                new MethodManager(
                    $methodResolver->reveal(),
                    new CustomExceptionCreator()
                ),
                $responseCreator
            ),
            new RawResponseSerializer(
                new ResponseNormalizer()
            ),
            $responseCreator
        );
    }

    /**
     * @param Prophet $prophet
     *
     * @return ObjectProphecy
     */
    private function getBasicMethod(Prophet $prophet)
    {
        $basicMethod = $prophet->prophesize(JsonRpcMethodInterface::class);
        $basicMethod->validateParams(Argument::cetera())
            ->willReturn(null);
        $basicMethod->apply(Argument::cetera())
            ->willReturn('basic-method-result');

        return $basicMethod;
    }

    /**
     * @param Prophet $prophet
     *
     * @return ObjectProphecy
     */
    private function getMethodWithRequiredParams(Prophet $prophet)
    {
        $methodWithParams = $prophet->prophesize(JsonRpcMethodInterface::class);
        $methodWithParams->validateParams(Argument::cetera())
            ->will(function ($args) {
                $params = $args[0];
                // Throw an exception only in case no params are given
                if (!is_array($params) || count($params) === 0) {
                    throw new \Exception('basic-method-with-param requires parameters');
                }

                return null;
            });
        $methodWithParams->apply(Argument::cetera())
            ->willReturn('basic-method-with-params-result');

        return $methodWithParams;
    }

    /**
     * @param Prophet $prophet
     *
     *
     * @return ObjectProphecy
     */
    private function getParamsValidationExceptionMethod(Prophet $prophet)
    {
        $paramsValidationExceptionMethod = $prophet->prophesize(JsonRpcMethodInterface::class);
        $paramsValidationExceptionMethod->validateParams(Argument::cetera())
            ->willThrow(new \Exception('method-that-throw-params-validation-exception validation exception'));

        return $paramsValidationExceptionMethod;
    }

    /**
     * @param Prophet $prophet
     *
     * @return ObjectProphecy
     */
    private function getExecutionExceptionMethod(Prophet $prophet)
    {
        $executionExceptionMethod = $prophet->prophesize(JsonRpcMethodInterface::class);
        $executionExceptionMethod->validateParams(Argument::cetera())
            ->willReturn(null);
        $executionExceptionMethod->apply(Argument::cetera())
            ->willThrow(new \Exception('method-that-throw-an-exception-during-execution execution exception'));

        return $executionExceptionMethod;
    }

    /**
     * @param Prophet $prophet
     *
     * @return ObjectProphecy
     */
    private function getCustomExecutionExceptionMethod(Prophet $prophet)
    {
        $customExecutionExceptionMethod = $prophet->prophesize(JsonRpcMethodInterface::class);
        $customExecutionExceptionMethod->validateParams(Argument::cetera())
            ->willReturn(null);
        $customExecutionExceptionMethod->apply(Argument::cetera())
            ->willThrow(new JsonRpcException(
                -32012,
                'A custom json-rpc error',
                [
                    'custom-data-property' => 'custom-data-value'
                ]
            ));

        return $customExecutionExceptionMethod;
    }

    /**
     * @param ObjectProphecy $methodResolver
     * @param string         $methodName
     * @param ObjectProphecy $method
     */
    private function addMethod(
        ObjectProphecy $methodResolver,
        string $methodName,
        ObjectProphecy $method
    ) {
        $methodResolver->resolve($methodName)->willReturn($method->reveal());
    }
}
