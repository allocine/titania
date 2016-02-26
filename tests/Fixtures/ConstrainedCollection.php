<?php

namespace Tests\Titania\Fixtures;

use Allocine\Titania\Type\Base\ObjectCollection;

class ConstrainedCollection extends ObjectCollection
{
    /**
     * @var string $internalObjectClass Fixed class for collection objects
     */
    protected $internalObjectClass = 'Tests\Titania\Fixtures\Constrained';
}
