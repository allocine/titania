<?php

namespace Tests\Titania\Type\Base;

use PHPUnit\Framework\TestCase;
use Tests\Titania\Fixtures\Constrained;
use Tests\Titania\Fixtures\ConstrainedNoAlias;
use Tests\Titania\Fixtures\ExtendedConstrained;
use Tests\Titania\Fixtures\ExtendedConstrainedWithSubclass;
use Tests\Titania\Fixtures\NotNullableConstraint;

class ConstrainedObjectTest extends TestCase
{
    /**
     * Test getAttribute method
     */

    public function testGetAttribute1()
    {
        $b = new Constrained();

        $this->assertNull($b->getAttribute('key1'));
        $this->assertEquals(3, $b->getAttribute('key2'));
        $this->assertNull($b->getAttribute('key1Alias'));

        $this->expectException('\Exception');
        $this->expectExceptionMessage(
            "Invalid attribute [key3] for constrained object [" .
            'Tests\Titania\Fixtures\Constrained' .
            "]."
        );

        $this->assertNull($b->getAttribute('key3'));
    }

    /**
     * Test getAttribute method
     */

    public function testGetAttribute2()
    {
        $b = new ExtendedConstrained();

        $this->assertNull($b->getAttribute('key1'));
        $this->assertNull($b->getAttribute('key1Alias'));
        $this->assertEquals(10, $b->getAttribute('key2'));
        $this->assertEquals(42, $b->getAttribute('key3'));
        $this->assertEquals(42, $b->getAttribute('key3Alias'));

        $this->expectException('\Exception');
        $this->expectExceptionMessage(
            "Invalid attribute [key4] for constrained object [" .
            'Tests\Titania\Fixtures\ExtendedConstrained' .
            "]."
        );

        $this->assertNull($b->getAttribute('key4'));
    }

    /**
     * Test hasAttribute method
     */

    public function testHasAttribute()
    {
        $b = new Constrained();

        $this->assertTrue($b->hasAttribute('key1'));
        $this->assertFalse($b->hasAttribute('key1Alias'));
        $this->assertTrue($b->hasAttribute('key2'));
        $this->assertFalse($b->hasAttribute('key3'));

        $b = new ExtendedConstrained();

        $this->assertTrue($b->hasAttribute('key1'));
        $this->assertFalse($b->hasAttribute('key1Alias'));
        $this->assertFalse($b->hasAttribute('key1AliasDouble'));
        $this->assertTrue($b->hasAttribute('key2'));
        $this->assertTrue($b->hasAttribute('key3'));
        $this->assertFalse($b->hasAttribute('key3Alias'));
        $this->assertFalse($b->hasAttribute('key4'));
    }

    /**
     * Test setAttribute method
     */

    public function testSetAttribute()
    {
        $b = new Constrained();

        $this->assertEquals(1, $b->setAttribute('key1', 1));
        $this->assertEquals(2, $b->setAttribute('key2', 2));

        $this->assertEquals(1, $b->getAttribute('key1'));
        $this->assertEquals(2, $b->getAttribute('key2'));

        /** Setting an alias */

        $this->assertEquals(12, $b->setAttribute('key1Alias', 12));
        $this->assertEquals(12, $b->getAttribute('key1'));

        /** Setting an invalid attribute */

        $this->expectException('\Exception');
        $this->expectExceptionMessage(
            "Invalid attribute [key3] for constrained object [" .
            'Tests\Titania\Fixtures\Constrained' .
            "]."
        );

        $b->setAttribute('key3', 1);
    }

    /**
     * Test setAttribute method
     */

    public function testSetAttributeSubClass()
    {
        $b = new ExtendedConstrainedWithSubclass();
        $c = new ExtendedConstrainedWithSubclass();

        $this->assertEquals(
            new ConstrainedNoAlias(),
            $b->setAttribute('keyA', null)
        );

        $this->assertEquals(
            new ConstrainedNoAlias('{"key1":12,"key2":45}'),
            $b->setAttribute('keyA', '{"key1":12,"key2":45}')
        );

        $this->assertEquals(12, $b->getAttribute('keyA')->getAttribute('key1'));
        $this->assertEquals(45, $b->getAttribute('keyA')->getAttribute('key2'));
    }

        /**
     * Get all the aliases of attributes
     */

    public function testGetAliases()
    {
        $b = new Constrained();

        $this->assertEquals(
            [ 'key1Alias' => 'key1' ],
            $b->getAliases()
        );

        $b = new ExtendedConstrained();

        $this->assertEquals(
            [
                'key1Alias'       => 'key1',
                'key1DoubleAlias' => 'key1',
                'key3Alias'       => 'key3'
            ],
            $b->getAliases()
        );
    }

    /**
     * @throws \Exception
     */

    public function testGetAlias()
    {
        $b = new ConstrainedNoAlias();

        $this->assertNull($b->getAlias('key1'));
        $this->assertNull($b->getAlias('key2'));

        $b = new Constrained();

        $this->assertEquals('key1Alias', $b->getAlias('key1'));
        $this->assertNull($b->getAlias('key2'));

        $b = new ExtendedConstrained();

        $this->assertEquals(
            [ 'key1Alias', 'key1DoubleAlias' ],
            $b->getAlias('key1')
        );
        $this->assertNull($b->getAlias('key2'));
        $this->assertEquals('key3Alias', $b->getAlias('key3'));

        $this->expectException('\Exception');
        $this->expectExceptionMessage(
            "Invalid attribute [key4] for constrained object [" .
            'Tests\Titania\Fixtures\ExtendedConstrained' .
            "]."
        );

        $b->getAlias('key4');
    }

    /**
     *
     */

    public function testResolveAlias()
    {
        $b = new Constrained();

        $this->assertEquals('key1', $b->resolveAlias('key1Alias'));

        $b = new ExtendedConstrained();

        $this->assertEquals('key1', $b->resolveAlias('key1Alias'));
        $this->assertEquals('key1', $b->resolveAlias('key1DoubleAlias'));
        $this->assertEquals('key3', $b->resolveAlias('key3Alias'));

        $this->expectException('\Exception');
        $this->expectExceptionMessage(
            "Invalid attributeAlias [key4] for constrained object [" .
            'Tests\Titania\Fixtures\ExtendedConstrained' .
            "]."
        );

        $b->resolveAlias('key4');
    }

    /**
     *
     */

    public function testToArrayWithAliases()
    {
        $b = new ConstrainedNoAlias();

        $this->assertEquals(
            [
                'key1' => null,
                'key2' => 3
            ],
            $b->toArrayWithAliases()
        );

        $b = new Constrained();

        $this->assertEquals(
            [
                'key1'      => null,
                'key2'      => 3,
                'key1Alias' => null,
            ],
            $b->toArrayWithAliases()
        );

        $b = new ExtendedConstrained();

        $this->assertEquals(
            [
                'key1'            => null,
                'key2'            => 10,
                'key3'            => 42,
                'key1Alias'       => null,
                'key3Alias'       => 42,
                'key1DoubleAlias' => null
            ],
            $b->toArrayWithAliases()
        );
    }

    /**
     *
     */

    public function testSimpleObject()
    {
        /** Test without compression */

        $b = new ConstrainedNoAlias();

        $this->assertEquals(
            (object) ['key1' => null, 'key2' => 3],
            $b->toSimpleObject(false)
        );

        $b = new Constrained();

        $this->assertEquals(
            (object) ['key1' => null, 'key2' => 3],
            $b->toSimpleObject(false)
        );

        $b = new ExtendedConstrained();

        $this->assertEquals(
            (object) ['key1' => null, 'key2' => 10, 'key3' => 42],
            $b->toSimpleObject(false)
        );

        /** Test with compression */

        $b = new ConstrainedNoAlias();

        $this->assertEquals(
            new \StdClass(),
            $b->toSimpleObject(true)
        );

        $b = new Constrained();

        $this->assertEquals(
            new \StdClass(),
            $b->toSimpleObject(true)
        );

        $b = new ExtendedConstrained();

        $this->assertEquals(
            new \StdClass(),
            $b->toSimpleObject(true)
        );

        /** Test with compression an modifications */

        $b = new ConstrainedNoAlias();

        $b->setAttribute('key2', 'coin');

        $this->assertEquals(
            (object) ['key2' => 'coin'],
            $b->toSimpleObject(true)
        );

        $b = new Constrained();

        $b->setAttribute('key2', 'coin');

        $this->assertEquals(
            (object) ['key2' => 'coin'],
            $b->toSimpleObject(true)
        );

        $b = new ExtendedConstrained();

        $b->setAttribute('key2', 'coin');

        $this->assertEquals(
            (object) ['key2' => 'coin'],
            $b->toSimpleObject(true)
        );

        /** Test with subclass attribute */

        $b = new ExtendedConstrainedWithSubclass();

        $this->assertEquals(
            (object) ['keyA' => (object) ['key1' => null, 'key2' => 3] ],
            $b->toSimpleObject(false)
        );

        $b->setAttribute('keyA', '{"key1":null,"key2":15}');

        $this->assertEquals(
            (object) ['keyA' => (object) ['key1' => null, 'key2' => 15] ],
            $b->toSimpleObject(false)
        );

        $b->setAttribute('keyA', '{"key1":12,"key2":15}');

        $this->assertEquals(
            (object) ['keyA' => (object) ['key1' => 12, 'key2' => 15] ],
            $b->toSimpleObject(false)
        );

        $b = new ExtendedConstrainedWithSubclass();

        $this->assertEquals(new \StdClass(), $b->toSimpleObject(true));

        $b->setAttribute('keyA', '{"key1":null,"key2":15}');

        $this->assertEquals(
            (object) ['keyA' => (object) ['key2' => 15] ],
            $b->toSimpleObject(true)
        );

        $b->setAttribute('keyA', '{"key1":12,"key2":15}');

        $this->assertEquals(
            (object) ['keyA' => (object) ['key1' => 12, 'key2' => 15] ],
            $b->toSimpleObject(true)
        );

    }

    /**
     *
     */

    public function testToJson()
    {
        /** Test without compression */

        $b = new ConstrainedNoAlias();

        $this->assertEquals(
            '{"key1":null,"key2":3}',
            $b->toJSon(false)
        );

        $b = new Constrained();

        $this->assertEquals(
            '{"key1":null,"key2":3}',
            $b->toJSon(false)
        );

        $b = new ExtendedConstrained();

        $this->assertEquals(
            '{"key1":null,"key2":10,"key3":42}',
            $b->toJSon(false)
        );

        /** Test with compression */

        $b = new ConstrainedNoAlias();

        $this->assertEquals(
            '{}',
            $b->toJSon(true)
        );

        $b = new Constrained();

        $this->assertEquals(
            '{}',
            $b->toJSon(true)
        );

        $b = new ExtendedConstrained();

        $this->assertEquals(
            '{}',
            $b->toJSon(true)
        );

        /** Test with compression an modifications */

        $b = new ConstrainedNoAlias();

        $b->setAttribute('key2', 'coin');

        $this->assertEquals(
            '{"key2":"coin"}',
            $b->toJSon(true)
        );

        $b = new Constrained();

        $b->setAttribute('key2', 'coin');

        $this->assertEquals(
            '{"key2":"coin"}',
            $b->toJSon(true)
        );

        $b = new ExtendedConstrained();

        $b->setAttribute('key2', 'coin');

        $this->assertEquals(
            '{"key2":"coin"}',
            $b->toJSon(true)
        );
    }

    /**
     *
     */

    public function testIsset()
    {
        $b = new Constrained();

        $this->assertTrue(isset($b->key1));
        $this->assertTrue(isset($b->key1Alias));
        $this->assertTrue(isset($b->key2));
        $this->assertFalse(isset($b->key3));

        $b = new ExtendedConstrained();

        $this->assertTrue(isset($b->key1));
        $this->assertTrue(isset($b->key1Alias));
        $this->assertTrue(isset($b->key1DoubleAlias));
        $this->assertTrue(isset($b->key2));
        $this->assertTrue(isset($b->key3));
        $this->assertTrue(isset($b->key3Alias));
        $this->assertFalse(isset($b->key4));
    }

    /**
     *
     */

    public function testHasAttributeClass()
    {
        $b = new Constrained();

        $this->assertFalse($b->hasAttributeClass('key1'));
        $this->assertFalse($b->hasAttributeClass('key2'));

        $b = new ExtendedConstrainedWithSubclass();

        $this->assertTrue($b->hasAttributeClass('keyA'));
    }

    /**
     *
     */

    public function testGetAttributeClass()
    {
        $b = new Constrained();

        $this->assertNull($b->getAttributeClass('key1'));
        $this->assertNull($b->getAttributeClass('key2'));

        $b = new ExtendedConstrainedWithSubclass();

        $this->assertInstanceOf(
            'Allocine\Titania\Type\Configuration\ClassDefinition',
            $b->getAttributeClass('keyA')
        );

        $this->assertEquals(
            $b->getAttributeClass('keyA')->getClass(),
            'Tests\Titania\Fixtures\ConstrainedNoAlias'
        );

        $this->assertFalse($b->getAttributeClass('keyA')->isNullable());
    }

    public function testNonNullableObject()
    {
        $a = new NotNullableConstraint();
        $b = new NotNullableConstraint();

        $this->assertNull($a->date);

        $a->date = 'now';

        $this->assertInstanceOf('\Datetime', $a->date);
        $b->setFromSomeData($a->toSimpleObject());

        $this->assertEquals($a->date->getTimestamp(), $b->date->getTimestamp());
    }

    public function testClone()
    {

        /** Test with subclass attribute */

        $b = new ExtendedConstrainedWithSubclass([
            'keyA' => [
                'key1' => 'COIN',
                'key2' => 'PAN'
            ]
        ]);

        // clone from $b

        $clone = clone($b);

        // copy from $b converted array

        $copy = new ExtendedConstrainedWithSubclass($b->toArray());

        $this->assertEquals($b, $copy);
        $this->assertNotSame($b, $copy);
        $this->assertEquals($b, $clone);
        $this->assertNotSame($b, $clone);

        $this->assertEquals($b->keyA, $copy->keyA);
        $this->assertNotSame($b->keyA, $copy->keyA);
        $this->assertEquals($b->keyA, $clone->keyA);
        $this->assertNotSame($b->keyA, $clone->keyA);
    }
}
