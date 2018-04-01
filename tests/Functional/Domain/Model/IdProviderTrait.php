<?php
namespace Tests\Functional\Domain\Model;

trait IdProviderTrait
{
    /**
     * @return array
     */
    public function provideValidIdData()
    {
        return [
            'a string' => [
                'id' => 'abcde',
            ],
            'an int' => [
                'id' => 4321,
            ],
        ];
    }
}
