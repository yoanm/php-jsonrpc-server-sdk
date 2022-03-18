<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcException
 */
class JsonRpcException extends \Exception implements JsonRpcExceptionInterface
{
    /** @var array<mixed> */
    private $data;

    /**
     * @param int          $code
     * @param string       $message
     * @param array<mixed> $data
     */
    public function __construct(int $code, string $message = '', array $data = [])
    {
        $this->data = $data;

        parent::__construct($message, $code);
    }

    public function getErrorCode() : int
    {
        return parent::getCode();
    }

    public function getErrorMessage() : string
    {
        return parent::getMessage();
    }

    /**
     * @return array<mixed>
     */
    public function getErrorData() : array
    {
        return $this->data;
    }
}
