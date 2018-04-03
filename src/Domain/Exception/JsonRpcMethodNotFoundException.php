<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcMethodNotFoundException
 */
class JsonRpcMethodNotFoundException extends JsonRpcException
{
    const CODE = -32601;

    /** @var string */
    private $methodName;

    /**
     * @param string $methodName
     */
    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;

        parent::__construct(self::CODE, 'Method not found');
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }
}
