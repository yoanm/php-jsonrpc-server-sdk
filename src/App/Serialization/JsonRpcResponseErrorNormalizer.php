<?php
namespace Yoanm\JsonRpcServer\App\Serialization;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcExceptionInterface;

/**
 * JsonRpcResponseErrorNormalizer prepares response data for the "unexpected" errors occur during request processing.
 *
 * It handles "internal server error" appearance in the response.
 * Instance of this class should be attached to {@see \Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer} only in "debug" mode,
 * since it will expose vital internal information to the API consumer.
 *
 * @see \Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer::normalizeError()
 */
class JsonRpcResponseErrorNormalizer
{
    /**
     * @var int maximum count of trace lines to be displayed.
     */
    private $maxTraceSize;

    /**
     * @var bool whether to show trace arguments.
     */
    private $showTraceArguments;

    /**
     * @var bool whether to simplify trace arguments representation.
     */
    private $simplifyTraceArguments;

    /**
     * @param int $maxTraceSize maximum count of trace lines to be displayed.
     * @param bool $showTraceArguments whether to show trace arguments.
     * @param bool $simplifyTraceArguments whether to simplify trace arguments representation.
     */
    public function __construct(int $maxTraceSize = 10, bool $showTraceArguments = true, bool $simplifyTraceArguments = true)
    {
        $this->maxTraceSize = $maxTraceSize;
        $this->showTraceArguments = $showTraceArguments;
        $this->simplifyTraceArguments = $simplifyTraceArguments;
    }

    /**
     * @param JsonRpcExceptionInterface $error
     * @return array
     */
    public function normalize(JsonRpcExceptionInterface $error) : array
    {
        return $this->composeDebugErrorData($error->getPrevious() ?? $error);
    }

    /**
     * @param \Throwable $error
     * @return array
     */
    private function composeDebugErrorData(\Throwable $error) : array
    {
        $data = [
            '_class' => get_class($error),
            '_code' => $error->getCode(),
            '_message' => $error->getMessage(),
        ];

        $trace = $this->filterErrorTrace($error->getTrace());
        if (!empty($trace)) {
            $data['_trace'] = $trace;
        }

        return $data;
    }

    /**
     * @param array $trace raw exception stack trace.
     * @return array simplified stack trace.
     */
    private function filterErrorTrace(array $trace): array
    {
        $trace = array_slice($trace, 0, $this->maxTraceSize);

        $result = [];
        foreach ($trace as $entry) {
            if (array_key_exists('args', $entry)) {
                if ($this->showTraceArguments) {
                    if ($this->simplifyTraceArguments) {
                        $entry['args'] = $this->simplifyArguments($entry['args']);
                    }
                } else {
                    unset($entry['args']);
                }
            }

            $result[] = $entry;
        }

        return $result;
    }

    /**
     * Converts arguments array to their simplified representation.
     *
     * @param array $args arguments array to be converted.
     * @return string string representation of the arguments array.
     */
    private function simplifyArguments(array $args) : string
    {
        $count = 0;

        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;

            if ($count >= 5) {
                if ($count > 5) {
                    unset($args[$key]);
                } else {
                    $args[$key] = '...';
                }

                continue;
            }

            if (is_object($value)) {
                $args[$key] = get_class($value);
            } elseif (is_bool($value)) {
                $args[$key] = $value ? 'true' : 'false';
            } elseif (is_string($value)) {
                if (strlen($value) > 64) {
                    $args[$key] = "'" . substr($value, 0, 64) . "...'";
                } else {
                    $args[$key] = "'" . $value . "'";
                }
            } elseif (is_array($value)) {
                $args[$key] = '[' . $this->simplifyArguments($value) . ']';
            } elseif ($value===null) {
                $args[$key] = 'null';
            } elseif (is_resource($value)) {
                $args[$key] = 'resource';
            }

            if (is_string($key)) {
                $args[$key] = "'" . $key . "' => " . $args[$key];
            } elseif ($isAssoc) {
                $args[$key] = $key.' => '.$args[$key];
            }
        }

        return implode(', ', $args);
    }
}
