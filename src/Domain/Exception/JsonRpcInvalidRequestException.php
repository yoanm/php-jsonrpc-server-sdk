<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcInvalidRequestException
 */
class JsonRpcInvalidRequestException extends JsonRpcException
{
    const CODE = -32600;

    const DATA_CONTENT_KEY = 'content';
    const DATA_DESCRIPTION_KEY = 'description';

    /**
     * @param mixed  $content Request parsed content
     * @param string $message Optional description
     */
    public function __construct($content, string $description = null)
    {
        $data = [self::DATA_CONTENT_KEY => $content];
        if ($description) {
            $data[self::DATA_DESCRIPTION_KEY] = $description;
        }
        parent::__construct(self::CODE, 'Parse error', $data);
    }
}
