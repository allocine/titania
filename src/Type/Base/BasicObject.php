<?php

/**
 * File AbstractBasicObject
 *
 * @package    Allocine\Titania
 * @subpackage Type
 * @version    1.0
 * @author     Yannick Le Guédart
 */

namespace Allocine\Titania\Type\Base;

/**
 * Class AbstractBasicObject
 *
 * This class handles abstract and unconstrained basic objects.
 *
 * @package    Allocine\Titania
 * @subpackage Type
 * @author     Yannick Le Guédart
 */

abstract class BasicObject implements
    \JsonSerializable,
    \ArrayAccess,
    ArrayConvertableInterface
{
    /**
     * Attributes of the basic object
     *
     * @var array
     */
    protected $attribute = [];

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
        if (! is_null($data)) {
            $this->setFromSomeData($data);
        }
    }

    /**
     * Magic getter to emulate ancient process. Can only get class value, or
     * attribute.
     *
     * @param string $name variable name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * Magic setter to emulate ancient process. Can only set attribute.
     *
     * @param string $name  variable name
     * @param mixed  $value variable value
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->setAttribute($name, $value);
    }

    /**
     * Magic caller to emulate ancient process. Can only be used to get
     * attribute via get* methods.
     *
     * @param string $name method name
     * @param array  $args method arguments
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function __call($name, array $args)
    {
        // if we have a getter, it can only return an attribute

        if ('get' === substr($name, 0, 3)) {
            return $this->getAttribute(lcfirst(substr($name, 3)));
        } elseif ('set' === substr($name, 0, 3)) {
            return $this->setAttribute(lcfirst(substr($name, 3)), $args[0]);
        }

        // else : exception

        throw new \Exception(
            "Invalid method [$name] for basic object [" .
            $this->getClass().
            "]."
        );
    }

    /**
     * Magic isseter to emulate ancient process. Can only get an attribute.
     *
     * @param string $name variable name
     *
     * @return mixed
     */

    public function __isset($name)
    {
        return isset($this->attribute[$name]);
    }

    /**
     * Sets the basic object from some data (public alias for setFromSomeData)
     *
     * @param mixed $data Whatever we can use to create the basic object
     *
     * @throws \Exception
     *
     * @return void
     */
    public function setFrom($data)
    {
        $this->setFromSomeData($data);
    }

    /**
     * Sets the basic object from some data
     *
     * @param mixed $data Whatever we can use to create the basic object
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function setFromSomeData($data)
    {
        if (is_string($data)) {
            // Si on a une chaine, on considère que c'est obligatoirement une
            // définition json. Si le décodage ne fonctionne pas, on envoie un
            // signal de détresse

            $properties = json_decode($data);

            if (is_null($properties)) {
                throw new \Exception(
                    'Invalid $data for ' . $this->getClass() . ' constructor'
                );
            }
        } elseif (is_object($data)) {
            // Si on a un objet, on récupère ses propriétés.

            if ($data instanceof BasicObject) {
                $properties = $data->toSimpleObject();
            } else {
                $properties = get_object_vars($data);
            }
        } elseif (is_array($data)) {
            // Si on a un un tableau, c'est vraisemblablement tout bon.

            $properties = $data;
        } else {
            // Alors là on a un truc, mais c'est pas prévu. Ca mérite bien une
            // petite exception.

            throw new \Exception(
                'Invalid $data for ' . $this->getClass() . ' constructor'
            );
        }

        $this->setFromArray($properties);
    }

    /**
     * Set object from an array. Attributes and properties are stored in the
     * right array, if valid
     *
     * @param array $data an array
     *
     * @return void
     */

    protected function setFromArray($data)
    {
        foreach ($data as $k => $v) {
            $this->setAttribute($k, $v);
        }
    }

    /**
     * Generate a flat object (the one used to create the json string used in
     * the admin project and sent by the internal API)
     *
     * @param boolean $compress When true, don't save attribute equals to the
     *                          default value
     *
     * @return object simple StdClass object
     */
    public function toSimpleObject($compress = true)
    {
        return (object) $this->toArray();
    }

    /**
     * Generate the array associated to the object
     *
     * @return array
     */
    public function toArray()
    {
        $return = [];

        foreach ($this->attribute as $key => $value) {
            if (is_object($value) && ($value instanceof BasicObject)) {
                $return[$key] = $value->toArray();
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Generate the json associated to the object
     *
     * @param boolean $compress When true, don't save attribute equals to the
     *                          default value
     *
     * @return string
     */
    public function toJson($compress = true)
    {
        return json_encode($this->toSimpleObject($compress));
    }

    /**
     * Get the class of the basic object
     *
     * @return string
     */
    public function getClass()
    {
        return get_called_class();
    }

    /**
     * Returns true because in this type of objects, all attribute are valids
     *
     * Can be overloaded in child classes
     *
     * @param string $name Attribute name
     *
     * @return boolean
     */
    public function hasAttribute($name)
    {
        return $this->__isset($name);
    }

    /**
     * Get an attribute of the basic object
     *
     * @param string $name Attribute name
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        if ($this->hasAttribute($name)) {
            return $this->attribute[$name];
        } else {
            return null;
        }
    }

    /**
     * set an property of the basic object
     *
     * @param string $name  Attribute name
     * @param mixed  $value Attribute value
     *
     * @return mixed
     */
    public function setAttribute($name, $value)
    {
        return ($this->attribute[$name] = $value);
    }

    /**
     * Implements the JsonSerializable interface
     */
    public function jsonSerialize()
    {
        return $this->toSimpleObject();
    }

    /**
     * Dumper method
     */
    public function __debugInfo()
    {
        return $this->attribute;
    }

    /**
     * Cloning method
     */
    public function __clone()
    {
        $class = $this->getClass();

        return new $class($this);
    }


    /**
     * Assigns a value to the specified offset
     *
     * @param string $offset The offset to assign the value to
     * @param mixed  $value  The value to set
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Whether or not an offset exists
     *
     * @param string $offset An offset to check for
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->hasAttribute($offset);
    }

    /**
     * Unset an offset
     *
     * @param string $offset The offset to unset
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->attribute[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param string $offset The offset to retrieve
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }
}
