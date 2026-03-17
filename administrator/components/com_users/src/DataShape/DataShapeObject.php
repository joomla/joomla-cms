<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\DataShape;

/**
 * Generic helper for handling data shapes in com_users
 *
 * @since 4.2.0
 */
abstract class DataShapeObject implements \ArrayAccess
{
    /**
     * Public constructor
     *
     * @param   array  $array  The data to initialise this object with
     *
     * @since 4.2.0
     */
    public function __construct(array $array = [])
    {
        if (!\is_array($array) && !($array instanceof self)) {
            throw new \InvalidArgumentException(\sprintf('%s needs an array or a %s object', __METHOD__, __CLASS__));
        }

        foreach (($array instanceof self) ? $array->asArray() : $array as $k => $v) {
            $this[$k] = $v;
        }
    }

    /**
     * Get the data shape as a key-value array
     *
     * @return array
     *
     * @since 4.2.0
     */
    public function asArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Merge another data shape object or key-value array into this object.
     *
     * @param   array|self  $newValues  The object or array to merge into self.
     *
     * @return  $this
     *
     * @since 4.2.0
     */
    public function merge($newValues): self
    {
        if (!\is_array($newValues) && !($newValues instanceof self)) {
            throw new \InvalidArgumentException(\sprintf('%s needs an array or a %s object', __METHOD__, __CLASS__));
        }

        foreach (($newValues instanceof self) ? $newValues->asArray() : $newValues as $k => $v) {
            if (!isset($this->{$k})) {
                continue;
            }

            $this[$k] = $v;
        }

        return $this;
    }

    /**
     * Magic getter
     *
     * @param   string  $name  The name of the property to retrieve
     *
     * @return  mixed
     *
     * @since 4.2.0
     */
    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);

        if (method_exists($this, $methodName)) {
            return $this->{$methodName};
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new \InvalidArgumentException(\sprintf('Property %s not found in %s', $name, __CLASS__));
    }

    /**
     * Magic Setter
     *
     * @param   string  $name   The property to set the value for
     * @param   mixed   $value  The property value to set it to
     *
     * @return mixed
     * @since 4.2.0
     */
    public function __set($name, $value)
    {
        $methodName = 'set' . ucfirst($name);

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}($value);
        }

        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }

        throw new \InvalidArgumentException(\sprintf('Property %s not found in %s', $name, __CLASS__));
    }

    /**
     * Is a property set?
     *
     * @param   string  $name  Property name
     *
     * @return  boolean  Does it exist in the object?
     * @since 4.2.0
     */
    public function __isset($name): bool
    {
        $methodName = 'get' . ucfirst($name);

        return method_exists($this, $methodName) || property_exists($this, $name);
    }

    /**
     * Does the property exist (array access)?
     *
     * @param   string  $offset  Property name
     *
     * @return  boolean
     * @since 4.2.0
     */
    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    /**
     * Get the value of a property (array access).
     *
     * @param   string  $offset  Property name
     *
     * @return  mixed
     * @since 4.2.0
     */
    public function offsetGet($offset): mixed
    {
        return $this->{$offset};
    }

    /**
     * Set the value of a property (array access).
     *
     * @param   string  $offset  Property name
     * @param   mixed   $value   Property value
     *
     * @return void
     * @since 4.2.0
     */
    public function offsetSet($offset, $value): void
    {
        $this->{$offset} = $value;
    }

    /**
     * Unset a property (array access).
     *
     * @param   string  $offset  Property name
     *
     * @return  void
     * @since 4.2.0
     */
    public function offsetUnset($offset): void
    {
        throw new \LogicException(\sprintf('You cannot unset members of %s', __CLASS__));
    }
}
