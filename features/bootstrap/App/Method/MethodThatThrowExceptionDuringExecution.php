<?php
namespace Tests\Functional\BehatContext\App\Method;

class MethodThatThrowExceptionDuringExecution extends AbstractMethod
{
    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        throw new \Exception('method-that-throw-an-exception-during-execution execution exception');
    }
}
