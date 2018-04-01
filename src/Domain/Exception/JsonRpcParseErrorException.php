<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcParseErrorException
 */
class JsonRpcParseErrorException extends JsonRpcException
{
    const CODE = -32700;

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
        $data = [
            self::DATA_CONTENT_KEY => $content,
        ];
        if ($parseErrorCode) {
            $data[self::DATA_ERROR_KEY][self::DATA_ERROR_CODE_KEY] = $parseErrorCode;
        }
        if ($parseErrorMessage) {
            $data[self::DATA_ERROR_KEY][self::DATA_ERROR_MESSAGE_KEY] = $parseErrorMessage;
        }

        parent::__construct(self::CODE, 'Parse error', $data);
    }
}
