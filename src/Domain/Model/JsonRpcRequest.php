<?php
namespace Yoanm\JsonRpcServer\Domain\Model;

use InvalidArgumentException;

/**
 * Class JsonRpcRequest
 */
class JsonRpcRequest
{
    /** @var string */
    private $jsonRpc;
    /** @var string */
    private $method;
    /** @var array */
    private $paramList = [];
    /** @var int|string|null */
    private $id = null;

    /**
     * @param string $jsonRpc
     * @param string $method
     */
    public function __construct(string $jsonRpc, string $method)
    {
        $this->jsonRpc = $jsonRpc;
        $this->method = $method;
    }

    /**
     * @param array $paramList
     *
     * @return self
     */
    public function setParamList(array $paramList) : self
    {
        $this->paramList = $paramList;

        return $this;
    }

    /**
     * @param mixed $id
     *
     * @throws InvalidArgumentException
     * @return self
     */
    public function setId($id) : self
    {
        if (!is_string($id) && !is_int($id)) {
            throw new InvalidArgumentException('Id must be either an int or a string');
        }

        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getJsonRpc() : string
    {
        return $this->jsonRpc;
    }

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParamList() : array
    {
        return $this->paramList;
    }

    /**
     * @return string|int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isNotification() : bool
    {
        return null === $this->id;
    }
}
