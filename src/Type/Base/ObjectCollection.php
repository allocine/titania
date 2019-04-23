<?php

/**
 * File ObjectCollection
 *
 * @package    Allocine\Titania
 * @subpackage Type
 * @version    1.0
 * @author     Yannick Le Guédart
 */

namespace Allocine\Titania\Type\Base;

/**
 * Class ObjectCollection
 *
 * This class handles abstract and unconstrained basic objects.
 *
 * @package    Allocine\Titania
 * @subpackage Type
 * @author     Yannick Le Guédart
 */

abstract class ObjectCollection
    extends \ArrayObject
    implements \JsonSerializable, ArrayConvertableInterface
{
    /**
     * @var string $internalObjectClass Fixed class for collection objects
     */
    protected $internalObjectClass;

    /**
     * Construct a new array object
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->setFromSomeData($data);
        $this->setFlags(self::ARRAY_AS_PROPS);
    }

    /**
     * @param mixed $index
     * @param mixed $value
     *
     * @throws \Exception
     */
    public function offsetSet($index, $value)
    {
        if (is_object($value)) {
            if ($this->internalObjectClass !== get_class($value)) {
                try {
                    $value = new $this->internalObjectClass($value);
                } catch (\Exception $e) {
                    throw new \Exception(
                        "Can't add a new item of class [" . get_class($value) .
                        "] to collection [" . get_called_class() .
                        "] because : " . $e->getMessage()
                    );
                }
            }
        } else {
            $value = new $this->internalObjectClass($value);
        }

        parent::offsetSet($index, $value);
    }

    /**
     * @param mixed $value
     *
     * @throws \Exception
     */
    public function append($value)
    {
        if (is_object($value)) {
            if (get_called_class() === get_class($value)) {
                foreach ($value as $v) {
                    $this->append($v);
                }
            } elseif ($this->internalObjectClass !== get_class($value)) {
                throw new \Exception(
                    "Can't append a new item of class [" . get_class($value) .
                    "] to collection [" . $this->getClass() . "]."
                );
            }
        } else {
            $value = new $this->internalObjectClass($value);
        }

        parent::append($value);
    }

    /**
     * Sets the the collection from some data
     *
     * @param mixed $data Whatever we can use to create the basic object
     *
     * @throws \Exception
     *
     * @return void
     */
    public function setFromSomeData($data)
    {
        if (is_string($data)) {
            // Si on a une chaine, on considère que c'est obligatoirement une
            // définition json. Si le décodage ne fonctionne pas, on envoie un
            // signal de détresse

            $properties = json_decode($data);

            if (is_null($properties)) {
                throw new \Exception(
                    'Unable to decode json in $data for ' . $this->getClass() . ' constructor'
                );
            }
        } elseif (is_object($data)) {
            // Si on a un objet, on récupère ses propriétés.
            if ($data instanceof ObjectCollection) {
                $properties = $data->toArray();
            } else {
                $properties = get_object_vars($data);
            }
        } elseif (is_array($data)) {
            // Si on a un un tableau, c'est vraisemblablement tout bon.
            $properties = $data;
        } else {
            throw new \Exception(sprintf(
                'Invalid $data for %s::__construct. Expected json, object or array. Got %s.',
                $this->getClass(),
                gettype($data)
            ));
        }

        $this->setFromArray($properties);
    }

    /**
     * Set object from an array.
     *
     ** @param array $data an array
     *
     * @return void
     */
    protected function setFromArray($data)
    {
        foreach ($data as $k => $v) {
            $this->offsetSet($k, $v);
        }
    }

    /**
     * Gets an array of objects.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * Gets a json representation of the collection
     */
    public function toJson()
    {
        return json_encode($this->toArray());
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
     * Implements the JsonSerializable interface
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Implements the JsonSerializable interface
     */
    public function __debugInfo()
    {
        return $this->toArray();
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
     * @param string|array $fields
     * @param int          $limit
     * @param string       $glue
     * @param string       $innerGlue
     *
     * @return string
     */
    public function format(
        $fields,
        $limit = null,
        $glue = ',',
        $innerGlue = ' '
    ) {
        $values = $this->toArray();

        // Splice array when needed
        if (! is_null($limit)) {
            $values = array_slice($values, 0, $limit);
        }

        // String $field is handled like a one item array to simplify things
        if (is_string($fields)) {
            $fields = [ $fields ];
        }

        $preImplode = [];

        foreach ($values as $object) {
            $items = [];

            foreach ($fields as $field) {
                $items[] = $object->{$field};
            }

            $preImplode[] = implode($innerGlue, $items);
        }

        return implode($glue, $preImplode);
    }
}
