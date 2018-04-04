<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Infra\Resolver\ArrayMethodResolver;
use Yoanm\JsonRpcServerPsr11Resolver\Domain\Model\ServiceNameResolverInterface;
use Yoanm\JsonRpcServerPsr11Resolver\Infra\Resolver\ContainerMethodResolver;

/**
 * Defines application features from the specific context.
 */
class ArrayResolverContext implements Context
{
    /** @var ArrayMethodResolver */
    private $arrayMethodResolver;
    /** @var ObjectProphecy[] */
    private $methodList = [];
    /** @var JsonRpcMethodInterface|ObjectProphecy|null */
    private $lastResult;

    /** @var Prophet */
    private $prophet;

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

        $this->arrayMethodResolver = new ArrayMethodResolver();
    }

    /**
     * @Given I add :methodName JSON-RPC method to ArrayMethodResolver
     */
    public function givenArrayMethodResolverWillResolveMethodNameToAJsonRpcMethod($methodName)
    {
        //Keep track of the method
        $this->methodList[$methodName] = $this->prophet->prophesize(JsonRpcMethodInterface::class);

        $this->arrayMethodResolver->addMethod($this->methodList[$methodName]->reveal(), $methodName);
    }

    /**
     * @When ArrayMethodResolver resolve :methodName
     */
    public function whenArrayMethodResolverResolve($methodName)
    {
        $this->lastResult = $this->arrayMethodResolver->resolve($methodName);
    }

    /**
     * @Then ArrayMethodResolver result should be :methodName JSON-RPC method
     * @Then ArrayMethodResolver result should be a null JSON-RPC method
     */
    public function thenArrayMethodResolverResultShouldJsonRpcMethod($methodName = null)
    {
        $method = null;
        if (null !== $methodName) {
            $method = $this->methodList[$methodName]->reveal();
        }
        Assert::assertSame($method, $this->lastResult);
    }
}
