<?php

/**
 * File AbstractConstrainedBasicObject
 *
 * @package    Allocine\Titania
 * @subpackage Type
 * @version    1.0
 * @author     Yannick Le Guédart
 */

namespace Allocine\Titania\Type\Base;

/**
 * Class AbstractConstrainedBasicObject
 *
 * This class handles individual basic object for users.
 *
 * @package    Allocine\Titania
 * @subpackage Type
 * @author     Yannick Le Guédart
 */

abstract class ConstrainedObject extends BasicObject
{
    /**
     * Has the init been done ?
     *
     * @var boolean
     */

    protected $initDone = false;

    /**
     * All valid properties of the object and their default value
     *
     * @var array
     */

    protected $attribute = [];

    /**
     * attributeDefault
     *
     * @var array
     */

    protected $attributeDefault = [];

    /**
     * All attributeAliasDefinition
     *
     * @var array
     */

    protected $attributeAlias = [];

    /**
     * attributeClass
     *
     * @var array
     */

    protected $attributeClass = [];

    /**
     * @return array
     */
    public static function getAttributeDefinition()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getAttributeAliasDefinition()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getAttributeClassDefinition()
    {
        return [];
    }

    /**
     * The class constructor
     *
     * Generate the AbstractBasicObject object from an json string, an array or
     * an object.
     * Whatever the input, we try to construct the basic object
     *
     * @param mixed $data Whatever we can use to create the basic object
     */

    public function __construct($data = null)
    {
        // Si on n'est pas dans la classe 'BasicObject', alors on a
        // potentiellement des attributs et des propriétés à ajouter. Ce n'est
        // pas là qu'on tentera de récupérer les données. On appelle donc la
        // méthode parente

        $this->init();

        if (!get_parent_class($this)) {
            parent::__construct($data);
        } else {
            // Création de l'objet à partir des données

            if (!is_null($data)) {
                $this->setFromSomeData($data);
            }
        }
    }

    /**
     * Magic isseter to emulate ancient process. Can only get an attributes.
     *
     * @param string $name variable name
     *
     * @return mixed
     */
    public function __isset($name)
    {
        return ($this->hasAttribute($name) || $this->isAlias($name));
    }

    /**
     * Initialize attributes and properties getting up to parents
     */
    protected function init()
    {
        $classDefinition = static::getAttributeClassDefinition();

        if (!$this->initDone) {
            foreach (static::getAttributeDefinition() as $k => $v) {
                if (array_key_exists($k, $classDefinition) && $classDefinition[$k]->isNullable() === false) {
                    $class = $classDefinition[$k]->getClass();

                    $this->attributeDefault[$k] = new $class($v);
                    $this->attribute[$k] = clone($this->attributeDefault[$k]);
                } else {
                    $this->attributeDefault[$k] = $v;
                    $this->attribute[$k] = $this->attributeDefault[$k];
                }
            }

            $this->attributeAlias = static::getAttributeAliasDefinition();
            $this->attributeClass = $classDefinition;

            $pc = get_called_class();

            while (get_class() !== ($pc = get_parent_class($pc))) {
                /** @var ConstrainedObject $pc */

                // Initialisation des attributs

                $pcClassDefinition = $pc::getAttributeClassDefinition();

                foreach ($pc::getAttributeDefinition() as $k => $v) {
                    /** Si l'attribut existe déjà, alors on l'ignore */

                    if (array_key_exists($k, $this->attribute)) {
                        continue;
                    }

                    if (array_key_exists($k, $pcClassDefinition)) {
                        $class = $pcClassDefinition[$k];

                        $this->attributeDefault[$k] = new $class($v);
                        $this->attribute[$k] = clone($this->attributeDefault[$k]);
                    } else {
                        $this->attributeDefault[$k] = $v;
                        $this->attribute[$k]= $this->attributeDefault[$k];
                    }
                }

                $this->attributeAlias = array_merge(
                    $pc::getAttributeAliasDefinition(),
                    $this->attributeAlias
                );
                $this->attributeClass = array_merge(
                    $pcClassDefinition,
                    $this->attributeClass
                );
            }

            $this->initDone = true;
        }
    }

    /**
     * Generate a flat object (the one used to create the json string used in
     * the admin project and sent by the internal API)
     *
     * @param boolean $compress When true, don't save attributes equals to the
     *                          default value
     *
     * @return object simple StdClass object
     */

    public function toSimpleObject($compress = true)
    {
        $o = new \stdClass();

        ksort($this->attribute);

        // Création des attributs

        foreach ($this->attribute as $k => $v) {
            if (!$compress) {
                if ($v instanceof BasicObject) {
                    $o->{$k} = $v->toSimpleObject($compress);
                } elseif ($v instanceof \DateTimeInterface) {
                    $o->{$k} = $v->format(\DATE_ATOM);
                } else {
                    $o->{$k} = $v;
                }
            } else {
                if ($v != $this->attributeDefault[$k]) {
                    if ($v instanceof BasicObject) {
                        $o->{$k} = $v->toSimpleObject($compress);
                    } elseif ($v instanceof \DateTimeInterface) {
                        $o->{$k} = $v->format(\DATE_ATOM);
                    } else {
                        $o->{$k} = $v;
                    }
                }
            }
        }

        return $o;
    }

    /**
     * Generate the json associated to the object
     *
     * @param boolean $compress When true, don't save attributes equals to the
     *                          default value
     *
     * @return string
     */

    public function toJSon($compress = true)
    {
        return json_encode($this->toSimpleObject($compress));
    }

    /**
     * Returns true if the attribute of the basic object exists
     *
     * @param string $name Attribute name
     *
     * @return boolean
     */

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attribute);
    }

    /**
     * Get an attribute of the basic object
     *
     * @param string $name Attribute name
     *
     * @throws \Exception
     *
     * @return mixed
     */

    public function getAttribute($name)
    {
        if ($this->isAlias($name)) {
            $name = $this->resolveAlias($name);
        }

        if ($this->hasAttribute($name)) {
            return $this->attribute[$name];
        } else {
            throw new \Exception(
                "Invalid attribute [$name] for constrained object [" .
                $this->getClass() .
                "]."
            );
        }
    }

    /**
     * set an property of the basic object
     *
     * @param string $name  Attribute name
     * @param mixed  $value Attribute value
     *
     * @throws \Exception
     *
     * @return mixed
     */

    public function setAttribute($name, $value)
    {
        if ($this->isAlias($name)) {
            $name = $this->resolveAlias($name);
        }

        if ($this->hasAttribute($name)) {
            $classDefinition = $this->getAttributeClass($name);

            if ($classDefinition) {

                $class = $classDefinition->getClass();

                if (is_null($value)) {
                    if ($classDefinition->isNullable() === false) {
                        throw new \Exception(sprintf(
                            "Attribute attribute [%s] of constrained object [%s] can not be null",
                            $name,
                            $this->getClass()
                        ));
                    }
                    else {
                        return ($this->attribute[$name] = new $class());
                    }
                }

                if (is_object($value)) {
                    if (
                        ($class === '\\' . get_class($value)) ||
                        ($class === get_class($value))
                    ) {
                        return ($this->attribute[$name] = $value);
                    } 
                }
                return ($this->attribute[$name] = new $class($value));
            } else {
                return ($this->attribute[$name] = $value);
            }
        } else {
            throw new \Exception(
                "Invalid attribute [$name] for constrained object [" .
                $this->getClass() .
                "]."
            );
        }
    }

    /**
     * @return array
     */

    public function getAliases()
    {
        return $this->attributeAlias;
    }

    /**
     * Return attributeAliasDefinition associated to an attribue or a string if there is only one
     *
     * @param $name
     *
     * @return array|string
     */

    public function getAlias($name)
    {
        if (!$this->hasAttribute($name)) {
            throw new \Exception(
                "Invalid attribute [$name] for constrained object [" .
                $this->getClass() .
                "]."
            );
        }

        $r = [];

        foreach ($this->getAliases() as $a => $n) {
            if ($n === $name) {
                $r[] = $a;
            }
        }

        if (count($r) === 1) {
            return $r[0];
        } elseif (count($r) === 0) {
            return null;
        }

        return $r;
    }

    /**
     * @param $name
     *
     * @return bool
     */

    public function isAlias($name)
    {
        return (array_key_exists($name, $this->attributeAlias));
    }

    /**
     * Return the real attribute linked to of an attributeAlias
     *
     * @param $name
     *
     * @return mixed
     *
     * @throws \Exception
     */

    public function resolveAlias($name)
    {
        if ($this->isAlias($name)) {
            return $this->attributeAlias[$name];
        } else {
            throw new \Exception(
                "Invalid attributeAlias [$name] for constrained object [" .
                $this->getClass() .
                "]."
            );
        }
    }

    /**
     * Generate the array associated to the object, including attributeAliasDefinition values
     *
     * @return array
     */

    public function toArrayWithAliases()
    {
        $r = $this->toArray();

        foreach ($this->getAliases() as $a => $k) {
            $r[$a] = $this->getAttribute($k);
        }

        return $r;
    }

    /**
     * Determine if an attribute has a linked class
     *
     * @param string $name
     *
     * @return bool
     */

    public function hasAttributeClass($name)
    {
        return array_key_exists($name, $this->attributeClass);
    }

    /**
     * Determine if an attribute has a linked class configuration
     *
     * @param string $name
     *
     * @return Allocine\Titania\Type\Base\Configuration\ClassDefinition
     */
    public function getAttributeClass($name)
    {
        if ($this->hasAttributeClass($name)) {
            return $this->attributeClass[$name];
        }

        return null;
    }

    /**
     * Unset an offset
     *
     * @param string $offset The offset to unset
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->setAttribute($offset, null);
        }
    }
}
