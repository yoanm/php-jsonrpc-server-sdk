<?php
namespace Tests\Functional\BehatContext\App\Method;

class MethodWithParamsValidationError extends AbstractMethod
{
    /**
     * {@inheritdoc}
     */
    public function validateParams(array $paramList) : array
    {
        return [
            [
                'path' => 'path-on-error',
                'message' => 'method-that-throw-params-validation-exception validation exception'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        throw new \UnexpectedValueException('This should never be called, as validation params must return errors !');
    }
}
