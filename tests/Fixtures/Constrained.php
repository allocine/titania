<?php

namespace Tests\Titania\Fixtures;

use Allocine\Titania\Type\Base\ConstrainedObject;

class Constrained extends ConstrainedObject
{
    public static function getAttributeDefinition()
    {
        return [
            'key1' => null,
            'key2' => 3
        ];
    }

    public static function getAttributeAliasDefinition()
    {
        return [
            'key1Alias' => 'key1'
        ];
    }

    public function setFromSomeData($data)
    {
        parent::setFromSomeData($data);
    }
}
