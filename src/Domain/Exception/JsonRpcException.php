<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcException
 */
class JsonRpcException extends \Exception implements JsonRpcExceptionInterface
{
    /** @var array */
    private $data;

    /**
     * @param int    $code
     * @param string $message
     * @param array  $data
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
     * @return array
     */
    public function getErrorData() : array
    {
        return $this->data;
    }
}
