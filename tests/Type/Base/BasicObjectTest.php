<?php

namespace Tests\Titania\Type\Base;

use Tests\Titania\Fixtures\Base;
use Tests\Titania\Fixtures\ExtendedBase;

class BasicObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getAttribute method
     */

    public function testGetAttribute()
    {
        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));
    }

    /**
     * Test hasAttribute method
     */

    public function testHasAttribute()
    {
        $b = new Base();

        $this->assertFalse($b->hasAttribute('key1'));
        $this->assertFalse($b->hasAttribute('key2'));
        $this->assertFalse($b->hasAttribute('key3'));

        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertTrue($b->hasAttribute('key1'));
        $this->assertTrue($b->hasAttribute('key2'));
        $this->assertFalse($b->hasAttribute('key3'));
    }

    /**
     * Test setAttribute method
     */

    public function testSetAttribute()
    {
        $b = new Base();

        $b->setAttribute('key1', 1);
        $b->setAttribute('key2', 2);

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));
    }

    /**
     * Test toArray method
     */

    public function testToArray()
    {
        $b = new Base();

        $this->assertEquals([], $b->toArray());

        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertEquals(['key1' => 1, 'key2' => 2], $b->toArray());
    }

    /**
     * Test toSimpleObject method
     */

    public function testToSimpleObject()
    {
        $b = new Base();

        $this->assertEquals(new \StdClass, $b->toSimpleObject());
        $this->assertEquals(new \StdClass, $b->toSimpleObject(false));

        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertEquals(
            (object)['key1' => 1, 'key2' => 2],
            $b->toSimpleObject()
        );
        $this->assertEquals(
            (object)['key1' => 1, 'key2' => 2],
            $b->toSimpleObject(false)
        );
    }

    /**
     * Test toJson method
     */

    public function testToJson()
    {
        $b = new Base();

        $this->assertEquals('{}', $b->toJson());
        $this->assertEquals('{}', $b->toJson(false));

        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertEquals('{"key1":1,"key2":2}', $b->toJson());
        $this->assertEquals('{"key1":1,"key2":2}', $b->toJson(false));
    }

    /**
     * Test jsonSerialize method
     */

    public function testJsonSerialize()
    {
        $b = new Base();

        $this->assertEquals(new \StdClass, $b->jsonSerialize());

        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertEquals(
            (object)['key1' => 1, 'key2' => 2],
            $b->jsonSerialize()
        );
    }

    /**
     * Test getClass method
     */

    public function testGetClass()
    {
        $b = new Base();

        $this->assertEquals(
            'Tests\Titania\Fixtures\Base',
            $b->getClass()
        );

        $eb = new ExtendedBase();

        $this->assertEquals(
            'Tests\Titania\Fixtures\ExtendedBase',
            $eb->getClass()
        );
    }

    /**
     * Test __debugInfo method
     */

    public function testDebugInfo()
    {
        $b = new Base();

        $this->assertEquals([], $b->__debugInfo());

        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertEquals(['key1' => 1, 'key2' => 2], $b->__debugInfo());
    }

    /**
     * Test __clone method
     */

    public function testClone()
    {
        $b = new Base();
        $bc = clone($b);

        $this->assertEquals($b, $bc);

        $bc->setAttribute('coin', 'PAN');

        $this->assertNotEquals($b, $bc);
    }

    /**
     * Test __get method
     */

    public function testGet()
    {
        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertEquals(1, $b->key1);
        $this->assertEquals(2, $b->key2);
        $this->assertNull($b->key3);
    }

    /**
     * Test __isset method
     */

    public function testIsset()
    {
        $b = new Base();

        $this->assertFalse(isset($b->key1));
        $this->assertFalse(isset($b->key2));
        $this->assertFalse(isset($b->key3));

        $b = new Base(['key1' => 1, 'key2' => 2]);

        $this->assertTrue(isset($b->key1));
        $this->assertTrue(isset($b->key2));
        $this->assertFalse(isset($b->key3));
    }

    /**
     * Test __set method
     */

    public function testSet()
    {
        $b = new Base();

        $b->key1 = 1;
        $b->key2 = 2;

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));
    }

    /**
     * Test __call method
     */

    public function testCall()
    {
        $b = new Base(['key1' => 1, 'key2' => 2]);
        $b->hasKey1 = function () {
            return $this->attribute['key1'];
        };

        /** getter */

        $this->assertEquals(1, $b->getKey1());
        $this->assertEquals(2, $b->getKey2());
        $this->assertEquals(2, $b->has_key1);

        /** setter */

        $b = new Base();

        $b->setKey1(1);
        $b->setKey2(2);

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));

        /** Invalid __call */

        $this->setExpectedException(
            '\Exception',
            "Invalid method [coin] for basic object [" .
            'Tests\Titania\Fixtures\Base' .
            "]."
        );

        $b->coin();
    }

    /**
     * Test setFromSomeData method
     */

    public function testSetFromSomeData()
    {
        /** from a json string */

        $b = new Base();

        $b->setFromSomeData('{"key1":1,"key2":2}');

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));

        /** from an array */

        $b = new Base();

        $b->setFromSomeData(['key1' => 1, 'key2' => 2]);

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));

        /** from an object */

        $b = new Base();

        $b->setFromSomeData((object)['key1' => 1, 'key2' => 2]);

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));

        /** from an object that is a BasicObject instance */

        $b = new Base();

        $b->setFromSomeData(new Base(['key1' => 1, 'key2' => 2]));

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));

        /** Invalid call */

        $b = new Base();

        $this->setExpectedException(
            '\Exception',
            'Invalid $data for Tests\Titania\Fixtures\Base constructor'
        );

        $b->setFromSomeData(null);
    }

    /**
     * Test setFromSomeData method
     */

    public function testSetFromSomeDataInvalidString()
    {
        /** Invalid string */

        $b = new Base();

        $this->setExpectedException(
            '\Exception',
            'Invalid $data for Tests\Titania\Fixtures\Base constructor'
        );

        $b->setFromSomeData('coin');
    }

    /**
     * Test __construct method (including parent constructor
     */

    public function testConstruct()
    {
        /** from a json string */

        $b = new ExtendedBase(['key1' => 1, 'key2' => 2]);

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key3'));
    }
}
