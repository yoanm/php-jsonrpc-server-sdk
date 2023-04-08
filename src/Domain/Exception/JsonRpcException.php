<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * JsonRpcException represents an error which can be rendered as a JsonRpc response.
 * It allows the specification of the particular data to be displayed making error more verbose.
 *
 * > Note: be careful about the data you expose via this mechanism, make sure you do not expose any vital information through it.
 *
 * @see \Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer
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
    public function __construct(int $code, string $message = '', array $data = [], ?\Throwable $previous = null)
    {
        $this->data = $data;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode() : int
    {
        return parent::getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorMessage() : string
    {
        return parent::getMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorData() : array
    {
        return $this->data;
    }
}
