<?php
namespace DemoApp\Method;

class BasicMethodWithRequiredParams extends AbstractMethod
{
    /**
     * {@inheritdoc}
     */
    public function validateParams(array $paramList) : array
    {
        $violationList = [];
        if (!is_array($paramList) || count($paramList) === 0) {
            $violationList[] = 'basic-method-with-param requires parameters';
        }

        return $violationList;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        return 'basic-method-with-params-result';
    }
}
