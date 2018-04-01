<?php
namespace Tests\Technical\Domain\Model;

trait IdProviderTrait
{
    /**
     * @return array
     */
    public function provideInvalidIdData()
    {
        return [
            'null' => [
                'invalidId' => null,
            ],
            'an object' => [
                'invalidId' => new \stdClass(),
            ],
            'an array' => [
                'invalidId' => [],
            ],
            'a float' => [
                'invalidId' => 1.2,
            ],
            'a boolean' => [
                'invalidId' => true,
            ],
        ];
    }
}
