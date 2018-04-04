<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tests\Functional\BehatContext\App\CustomMethodResolver;
use Tests\Functional\BehatContext\App\JsonRpcMethod;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\JsonRpcServer\Infra\Symfony\DependencyInjection\JsonRpcServerExtension;

/**
 * Defines application features from the specific context.
 */
class SymfonyExtensionContext implements Context
{
    const CUSTOM_METHOD_RESOLVER_SERVICE_ID = 'custom-method-resolver-service';

    /** @var JsonRpcServerExtension */
    private $extension;
    /** @var Prophet */
    private $prophet;
    /** @var ContainerBuilder */
    private $containerBuilder;
    /** @var MethodResolverInterface|ObjectProphecy */
    private $customMethodResolverDefinition;
    /** @var ObjectProphecy[] */
    private $methodList = [];
    /** @var mixed */
    private $endpoint;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->prophet = new Prophet();
    }

    /**
     * @Given I process the symfony extension
     */
    public function givenIProcessTheSymfonyExtension()
    {
        (new JsonRpcServerExtension())->load([], $this->getContainerBuilder());
    }

    /**
     * @Given I inject my :methodName JSON-RPC method into default method resolver definition
     */
    public function givenIInjectMyJsonRpcMethodIntoDefaultMethodResolverDefinition($methodName)
    {
        $this->injectJsonRpcMethodToResolverService(
            $methodName,
            $this->createJsonRpcMethodDefinition(),
            JsonRpcServerExtension::DEFAULT_METHOD_RESOLVER_SERVICE_NAME
        );
    }

    /**
     * @Given I inject my :methodName JSON-RPC method into default method resolver instance
     */
    public function givenIInjectMyJsonRpcMethodIntoDefaultMethodResolverInstance($methodName)
    {
        $this->injectJsonRpcMethodToResolverService(
            $methodName,
            $this->createJsonRpcMethod(),
            JsonRpcServerExtension::DEFAULT_METHOD_RESOLVER_SERVICE_NAME
        );
    }

    /**
     * @Given I tag my custom method resolver service with :tagName
     */
    public function givenITagMyCustomMethodResolverServiceWith($tagName)
    {
        $this->getContainerBuilder()->findDefinition(self::CUSTOM_METHOD_RESOLVER_SERVICE_ID)->addTag($tagName);
    }

    /**
     * @Given I inject my :methodName JSON-RPC method into my custom method resolver instance
     */
    public function givenIInjectMyJsonRpcMethodIntoMyCustomMethodResolverInstance($methodName)
    {
        $this->injectJsonRpcMethodToResolverService(
            $methodName,
            $this->createJsonRpcMethod(),
            self::CUSTOM_METHOD_RESOLVER_SERVICE_ID
        );
    }

    /**
     * @Given I inject my :methodName JSON-RPC method into my custom method resolver definition
     */
    public function givenIInjectMyJsonRpcMethodIntoMyCustomMethodResolverDefinition($methodName)
    {
        $this->injectJsonRpcMethodToResolverService(
            $methodName,
            $this->createJsonRpcMethodDefinition(),
            self::CUSTOM_METHOD_RESOLVER_SERVICE_ID
        );
    }

    /**
     * @Given I have a JSON-RPC method service definition with :tagName tag and following tag attributes:
     */
    public function givenITagMyJsonRpcMethodServiceWithTagAndFollowingAttributes($tagName, PyStringNode $tagAttributeNode)
    {
        $definition = $this->createJsonRpcMethodDefinition()
            ->addTag($tagName, json_decode($tagAttributeNode, true));

        $this->getContainerBuilder()->setDefinition(uniqid(), $definition);
    }

    /**
     * @When I load endpoint from :serviceId service
     */
    public function whenILoadEndpointFromService($serviceId)
    {
        $this->getContainerBuilder()->compile();

        $this->endpoint = $this->getContainerBuilder()->get($serviceId);
    }

    /**
     * @Then endpoint should respond to following JSON-RPC methods:
     */
    public function thenEndpointShouldResponseToFollowingJsonRpcMethods(TableNode $methodList)
    {
        Assert::assertInstanceOf(JsonRpcEndpoint::class, $this->endpoint);

        $methodList = array_map('array_shift', $methodList->getRows());

        $this->assertEndpointRespondToCalls($this->endpoint, $methodList);
    }

    /**
     * @param JsonRpcEndpoint $endpoint
     * @param array           $methodNameList
     */
    private function assertEndpointRespondToCalls(JsonRpcEndpoint $endpoint, array $methodNameList)
    {
        foreach ($methodNameList as $methodName) {
            $requestId = uniqid();
            Assert::assertSame(
                json_encode(
                    [
                        'jsonrpc' => '2.0',
                        'id' => $requestId,
                        'result' => 'OK'
                    ]
                ),
                $endpoint->index(
                    json_encode(
                        [
                            'jsonrpc' => '2.0',
                            'id' => $requestId,
                            'method' => $methodName
                        ]
                    )
                )
            );
        }
    }

    /**
     * @return JsonRpcMethod
     */
    private function createJsonRpcMethod()
    {
        return new JsonRpcMethod();
    }

    /**
     * @return Definition
     */
    private function createJsonRpcMethodDefinition()
    {
        return new Definition(JsonRpcMethod::class);
    }

    /**
     * @param string $methodName
     * @param JsonRpcMethod|Definition $method
     */
    private function injectJsonRpcMethodToResolverService($methodName, $method, $resolverServiceId)
    {
        if ($method instanceof Definition) {
            $this->getContainerBuilder()
                ->getDefinition($resolverServiceId)
                ->addMethodCall('addMethod', [$method, $methodName]);
        } else {
            $this->getContainerBuilder()
                ->get($resolverServiceId)
                ->addMethod($method, $methodName);
        }
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainerBuilder()
    {
        if (!$this->containerBuilder) {
            $this->containerBuilder = new ContainerBuilder();
            // Add definition of custom resolver (without tags)
            $customResolverDefinition = (new Definition(CustomMethodResolver::class))->setPrivate(false);
            $this->containerBuilder->setDefinition(self::CUSTOM_METHOD_RESOLVER_SERVICE_ID, $customResolverDefinition);
        }

        return $this->containerBuilder;
    }
}
