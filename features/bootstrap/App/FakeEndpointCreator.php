<?php
namespace Tests\Functional\BehatContext\App;

use Prophecy\Argument;
use Prophecy\Prophet;
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

class FakeEndpointCreator
{
    /**
     * @param MethodResolverInterface $methodResolver
     *
     * @return JsonRpcEndpoint
     */
    public function create(MethodResolverInterface $methodResolver) : JsonRpcEndpoint
    {
        $responseCreator = new ResponseCreator();

        $prophet = new Prophet();

        $basicMethod = $prophet->prophesize(JsonRpcMethodInterface::class);
        $basicMethod->validateParams(Argument::cetera())
            ->willReturn(null);
        $basicMethod->apply(Argument::cetera())
            ->willReturn('basic-method-result');

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

        $paramsValidationExceptionMethod = $prophet->prophesize(JsonRpcMethodInterface::class);
        $paramsValidationExceptionMethod->validateParams(Argument::cetera())
            ->willThrow(new \Exception('method-that-throw-params-validation-exception validation exception'));

        $executionExceptionMethod = $prophet->prophesize(JsonRpcMethodInterface::class);
        $executionExceptionMethod->validateParams(Argument::cetera())
            ->willReturn(null);
        $executionExceptionMethod->apply(Argument::cetera())
            ->willThrow(new \Exception('method-that-throw-an-exception-during-execution execution exception'));
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

        $methodResolver->addMethod($basicMethod->reveal(), 'basic-method');
        $methodResolver->addMethod($methodWithParams->reveal(), 'basic-method-with-params');
        $methodResolver->addMethod(
            $paramsValidationExceptionMethod->reveal(),
            'method-that-throw-params-validation-exception'
        );
        $methodResolver->addMethod(
            $executionExceptionMethod->reveal(),
            'method-that-throw-an-exception-during-execution'
        );

        $methodResolver->addMethod(
            $customExecutionExceptionMethod->reveal(),
            'method-that-throw-a-custom-jsonrpc-exception-during-execution'
        );

        return new JsonRpcEndpoint(
            new RawRequestSerializer(
                new RequestDenormalizer()
            ),
            new RequestHandler(
                new MethodManager(
                    $methodResolver,
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
}
