<?php
namespace Yoanm\JsonRpcServer\App\Manager;

use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidParamsException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcMethodNotFoundException;

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
     * @param string $methodName
     * @param array  $paramList
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

        $this->validateParamsIfNeeded($method, $methodName, $paramList);

        try {
            return $method->apply($paramList);
        } catch (\Exception $applyException) {
            throw $this->customExceptionCreator->createFor($applyException);
        }
    }

    /**
     * @param JsonRpcMethodInterface $method
     * @param string                 $methodName
     * @param array                  $paramList
     *
     * @throws JsonRpcInvalidParamsException
     *
     * @return void
     */
    private function validateParamsIfNeeded(JsonRpcMethodInterface $method, string $methodName, array $paramList)
    {
        if (is_array($paramList)) {
            try {
                $method->validateParams($paramList);
            } catch (\Exception $validationException) {
                throw new JsonRpcInvalidParamsException(
                    $methodName,
                    $validationException->getMessage()
                );
            }
        }
    }
}
