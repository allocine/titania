<?php

namespace Tests\Titania\Fixtures;

use Allocine\Titania\Type\Base\ConstrainedObject;
use Allocine\Titania\Type\Configuration\ClassDefinition;

class ExtendedConstrainedWithSubclass extends ConstrainedObject
{
    public static function getAttributeDefinition()
    {
        return [
            'keyA' => null
        ];
    }

    public static function getAttributeClassDefinition()
    {
        return [
            'keyA' => new ClassDefinition([ 'class' => 'Tests\Titania\Fixtures\ConstrainedNoAlias' ])
        ];
    }
}
