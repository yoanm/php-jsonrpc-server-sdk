<?php
namespace DemoApp\Method;

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
