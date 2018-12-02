<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;

class AbstractContext implements Context
{
    public function jsonDecode($encodedData)
    {
        $decoded = json_decode($encodedData, true);

        if (JSON_ERROR_NONE != json_last_error()) {
            throw new \Exception(
                json_last_error_msg(),
                json_last_error()
            );
        }

        return $decoded;
    }
}
