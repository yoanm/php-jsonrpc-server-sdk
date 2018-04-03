<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcParseErrorException
 */
class JsonRpcParseErrorException extends JsonRpcException
{
    const CODE = -32700;

    /** @var mixed */
    private $content;
    /** @var mixed */
    private $parseErrorCode;
    /** @var string */
    private $parseErrorMessage;

    const DATA_CONTENT_KEY = 'content';
    const DATA_ERROR_KEY = 'error';
    const DATA_ERROR_CODE_KEY = 'code';
    const DATA_ERROR_MESSAGE_KEY = 'message';

    /**
     * @param string $content
     * @param mixed  $parseErrorCode
     * @param mixed  $parseErrorMessage
     */
    public function __construct(string $content, $parseErrorCode = null, $parseErrorMessage = null)
    {
        $this->content = $content;
        $this->parseErrorCode = $parseErrorCode;
        $this->parseErrorMessage = $parseErrorMessage;

        parent::__construct(self::CODE, 'Parse error');
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getParseErrorCode()
    {
        return $this->parseErrorCode;
    }

    /**
     * @return string
     */
    public function getParseErrorMessage()
    {
        return $this->parseErrorMessage;
    }
}
