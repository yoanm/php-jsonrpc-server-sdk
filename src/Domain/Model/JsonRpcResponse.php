<?php
namespace Yoanm\JsonRpcServer\Domain\Model;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;

/**
 * Class JsonRpcResponse
 */
class JsonRpcResponse
{
    const DEFAULT_VERSION = '2.0';

    /** @var string */
    private $jsonRpc;
    /** @var mixed */
    private $result = null;
    /** @var null|JsonRpcExceptionInterface */
    private $error = null;
    /** @var mixed */
    private $id = null;
    /** @var bool */
    private $isNotification = false;

    /**
     * @param string $jsonRpc
     */
    public function __construct(string $jsonRpc = self::DEFAULT_VERSION)
    {
        $this->jsonRpc = $jsonRpc;
    }

    /**
     * @param mixed $result
     *
     * @return self
     */
    public function setResult($result) : self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @param JsonRpcExceptionInterface $error
     *
     * @return self
     */
    public function setError(JsonRpcExceptionInterface $error) : self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @param mixed $id
     *
     * @return self
     */
    public function setId($id) : self
    {
        if (!is_string($id) && !is_int($id)) {
            throw new \InvalidArgumentException('Id must be either an int or a string');
        }

        $this->id = $id;

        return $this;
    }

    /**
     * @param bool $isNotification
     *
     * @return self
     */
    public function setIsNotification(bool $isNotification) : self
    {
        $this->isNotification = $isNotification;

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
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return JsonRpcExceptionInterface|null
     */
    public function getError() : ?JsonRpcExceptionInterface
    {
        return $this->error;
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
        return $this->isNotification;
    }
}
