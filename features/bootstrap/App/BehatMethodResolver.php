<?php
namespace Tests\Functional\BehatContext\App;

use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;

/**
 * Defines application features from the specific context.
 */
class BehatMethodResolver implements JsonRpcMethodResolverInterface
{
    /** @var JsonRpcMethodInterface[] */
    private $methodList = [];

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName)
    {
        return array_key_exists($methodName, $this->methodList)
            ? $this->methodList[$methodName]
            : null
        ;
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
