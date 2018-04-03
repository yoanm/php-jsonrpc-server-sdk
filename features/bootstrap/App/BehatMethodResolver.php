<?php
namespace Tests\Functional\BehatContext\App;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;

/**
 * Defines application features from the specific context.
 */
class BehatMethodResolver implements MethodResolverInterface
{
    /** @var JsonRpcMethodInterface[] */
    private $methodList = [];

    /**
     * @param string $methodName
     *
     * @return JsonRpcMethodInterface
     *
     * @throws JsonRpcMethodNotFoundException
     */
    public function resolve(string $methodName) : JsonRpcMethodInterface
    {
        if (!isset($this->methodList[$methodName])) {
            throw new JsonRpcMethodNotFoundException($methodName);
        }

        return $this->methodList[$methodName];
    }

    /**
     * @param JsonRpcMethodInterface $method
     * @param string                 $methodName
     */
    public function addMethod(JsonRpcMethodInterface $method, string $methodName)
    {
        $this->methodList[$methodName] = $method;
    }
}
