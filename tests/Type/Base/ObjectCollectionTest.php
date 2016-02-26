<?php

namespace Tests\Titania\Type\Base;

use Tests\Titania\Fixtures\ConstrainedCollection;

class ObjectCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test format method
     */
    public function testFormat()
    {
        $collection = new ConstrainedCollection(
            '[
                {"key1":1,"key2":7},
                {"key1":2,"key2":8},
                {"key1":3,"key2":9},
                {"key1":4,"key2":10},
                {"key1":5,"key2":11},
                {"key1":6,"key2":12}
            ]'
        );

        $this->assertEquals('1,2,3,4,5,6', $collection->format('key1'));
        $this->assertEquals('1,2,3', $collection->format('key1', 3));
        $this->assertEquals('1_2_3', $collection->format('key1', 3, '_'));
        $this->assertEquals('7,8,9,10,11,12', $collection->format('key2'));
        $this->assertEquals('7,8,9,10', $collection->format('key2', 4));
        $this->assertEquals('7+8+9+10', $collection->format('key2', 4, '+'));

        $this->assertEquals(
            '1 7,2 8,3 9,4 10,5 11,6 12',
            $collection->format([ 'key1', 'key2' ])
        );
        $this->assertEquals(
            '1 7,2 8,3 9',
            $collection->format([ 'key1', 'key2' ], 3)
        );
        $this->assertEquals(
            '1 7_2 8_3 9',
            $collection->format([ 'key1', 'key2' ], 3, '_')
        );
        $this->assertEquals(
            '1**7_2**8_3**9_4**10',
            $collection->format([ 'key1', 'key2' ], 4, '_', '**')
        );
    }
}
