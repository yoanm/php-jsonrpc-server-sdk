<?php
namespace Yoanm\JsonRpcServer\App\Manager;

use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidParamsException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;

/**
 * Class MethodManager
 */
class MethodManager
{
    /** @var MethodResolverInterface */
    private $methodResolver;
    /** @var CustomExceptionCreator */
    private $customExceptionCreator;

    /**
     * @param MethodResolverInterface $methodResolver
     * @param CustomExceptionCreator  $customExceptionCreator
     */
    public function __construct(MethodResolverInterface $methodResolver, CustomExceptionCreator $customExceptionCreator)
    {
        $this->methodResolver = $methodResolver;
        $this->customExceptionCreator = $customExceptionCreator;
    }

    /**
     * @param string      $methodName
     * @param array|null  $paramList
     *
     * @return mixed
     *
     * @throws JsonRpcInvalidParamsException
     * @throws JsonRpcMethodNotFoundException
     * @throws JsonRpcExceptionInterface
     */
    public function apply(string $methodName, array $paramList = null)
    {
        $method = $this->methodResolver->resolve($methodName);

        if (!$method instanceof JsonRpcMethodInterface) {
            throw new JsonRpcMethodNotFoundException($methodName);
        }

        $this->validateParams($method, $paramList);

        try {
            return $method->apply($paramList);
        } catch (\Exception $applyException) {
            throw $this->customExceptionCreator->createFor($applyException);
        }
    }

    /**
     * @param JsonRpcMethodInterface $method
     * @param array|null             $paramList
     *
     * @throws JsonRpcInvalidParamsException
     *
     * @return void
     */
    private function validateParams(JsonRpcMethodInterface $method, array $paramList = null)
    {
        $violationList = $method->validateParams($paramList ?? []);

        if (count($violationList)) {
            throw new JsonRpcInvalidParamsException($violationList);
        }
    }
}
