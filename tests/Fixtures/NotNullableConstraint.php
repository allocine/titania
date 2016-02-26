<?php

namespace Tests\Titania\Fixtures;

use Allocine\Titania\Type\Base\ConstrainedObject;
use Allocine\Titania\Type\Configuration\ClassDefinition;

/**
 * Description of NotNullableConstraint
 *
 * @author Xavier HAUSHERR <xhausherr@allocine.fr>
 */
class NotNullableConstraint extends ConstrainedObject
{
    public static function getAttributeDefinition()
    {
        return [
            'date' => null,
        ];
    }

    public static function getAttributeClassDefinition()
    {
        return [
            'date' => new ClassDefinition([ 'class' => '\DateTime', 'nullable' => true ]),
        ];
    }

    public function setFromSomeData($data)
    {
        parent::setFromSomeData($data);
    }
}
