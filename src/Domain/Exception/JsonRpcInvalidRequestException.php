<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcInvalidRequestException
 */
class JsonRpcInvalidRequestException extends JsonRpcException
{
    const CODE = -32600;

    /** @var mixed */
    private $content;
    /** @var string */
    private $description;

    /**
     * @param mixed  $content
     * @param string $description Optional description of the issue
     */
    public function __construct($content, string $description = '')
    {
        $this->content = $content;
        $this->description = $description;

        parent::__construct(self::CODE, 'Invalid request');
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}
