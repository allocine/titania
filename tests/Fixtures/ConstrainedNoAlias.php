<?php

namespace Tests\Titania\Fixtures;

use Allocine\Titania\Type\Base\ConstrainedObject;

class ConstrainedNoAlias extends ConstrainedObject
{
    public static function getAttributeDefinition()
    {
        return [
            'key1' => null,
            'key2' => 3,
        ];
    }
}
