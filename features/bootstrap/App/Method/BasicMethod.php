<?php
namespace Tests\Functional\BehatContext\App\Method;

class BasicMethod extends AbstractMethod
{
    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        return 'basic-method-result';
    }
}
