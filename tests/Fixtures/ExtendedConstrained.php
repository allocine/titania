<?php

namespace Tests\Titania\Fixtures;

class ExtendedConstrained extends Constrained
{
    public static function getAttributeDefinition()
    {
        return [
            'key2' => 10,
            'key3' => 42
        ];
    }

    public static function getAttributeAliasDefinition()
    {
        return [
            'key3Alias'       => 'key3',
            'key1DoubleAlias' => 'key1'
        ];
    }
}
