<?php

namespace Joomla\CMS\Object;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Platform Object Class with dynamic property simulation
 *
 * This class allows for simple but smart objects with get and set methods. It uses magic getters
 * and setters to avoid problems in PHP 8.2 where creation of dynamic object properties is
 * deprecated and PHP 9.0 where creation of dynamic object properties is removed and causes a
 * Fatal error exception.
 *
 * This class also provides an internal error handler which is, however, deprecated and should
 * eventually go away -- unless we decide that non-fatal warnings need to be logged internally
 * instead of raising exceptions.
 *
 * This is a (mostly) drop-in replacement for the deprecated \Joomla\CMS\Object\CMSObject class with
 * a few changes:
 * - Conversion to string now returns a JSON-serialised form of the properties (dynamic and
 *   concrete) instead of the class name.
 * - Using the underscore (`_`) prefix for "private" properties is deprecated. Use property
 *   visibility instead.
 * - getProperties(true) will return dynamic properties with underscore prefixes as they are no
 *   longer considered "private" (see above).
 * - Property names MUST NOT have the prefix `joomlareserved_`. This is a reserved prefix for
 *   internal use. This requirement is not enforced but if you use this prefix in your properties
 *   your objects might break in the future.
 * - You can promote setting errors to throw RuntimeException using the `useExceptions` method.
 *
 * @since       __DEPLOY_VERSION__
 */
class CMSDynamicObject implements \JsonSerializable
{
    /**
     * Selects any dynamic or concrete property.
     *
     * @var   int
     * @since __DEPLOY_VERSION__
     */
    public const IS_ANY = 0;

    /**
     * Selects any dynamic property.
     *
     * @var   int
     * @since __DEPLOY_VERSION__
     */
    public const IS_DYNAMIC = 1;

    /**
     * Selects any concrete property.
     *
     * @var   int
     * @since __DEPLOY_VERSION__
     */
    public const IS_CONCRETE = 2;

    /**
     * An array of error messages or Exception objects.
     *
     * @var         array
     * @since       __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will always use exceptions
     */
    protected array $joomlareserved_errors = [];

    /**
     * Should I throw exceptions instead of setting the error messages internally?
     *
     * @var   bool
     * @since __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will always use exceptions
     */
    protected bool $joomlareserved_use_exceptions = false;

    /**
     * Dynamically declared properties and their values
     *
     * @var   array
     * @since __DEPLOY_VERSION__
     */
    protected array $joomlareserved_dynamic_properties = [];

    /**
     * Class constructor, overridden in descendent classes.
     *
     * @param   array|object|null  $properties  An associative array or another object to set the
     *                                          initial properties of the object.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(array|object|null $properties = null)
    {
        if ($properties !== null) {
            $this->setProperties($properties);
        }
    }

    /**
     * Magic method to convert the object to a string gracefully.
     *
     * By default, this returns a JSON string. This will only work correctly if all property values
     * are JSON-serializable (scalars, primitive types, objects implementing \JsonSerializable).
     *
     * Subclasses of CMSObject should declare their own method to convert to string.
     *
     * @return  string  JSON-encoded data.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * Magic getter.
     *
     * Allows getting the dynamically declared properties' values as if they were concrete.
     *
     * @param   string  $name  The name of the dynamic property to get.
     *
     * @return  mixed
     * @since   __DEPLOY_VERSION__
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Magic setter.
     *
     * Allows setting the dynamically declared properties' values as if they were concrete.
     *
     * @param   string  $name   The property name
     * @param   mixed   $value  The value to set
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Allows using isset() and empty() on dynamically declared properties as if they were concrete.
     *
     * @param   string  $name  The property name
     *
     * @return  bool
     * @since   __DEPLOY_VERSION__
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Allows using unset() to remove dynamically declared properties.
     *
     * @param   string  $name  The property name
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    public function __unset(string $name): void
    {
        $this->remove($name);
    }

    /**
     * Returns the data to be serialised by json_encode().
     *
     * @return  mixed
     * @since   __DEPLOY_VERSION__
     */
    public function jsonSerialize(): mixed
    {
        return $this->getProperties();
    }

    /**
     * Sets a default value if not already assigned i.e. if it's not NULL
     *
     * @param   string      $property  The name of the property.
     * @param   mixed|null  $default   The default value.
     *
     * @return  mixed  The previous value of this property
     * @throws  \OutOfBoundsException  If property starts with 'joomlareserved_'
     *
     * @since   __DEPLOY_VERSION__
     * @deprecated  7.0  Use has(), get() and set() instead.
     */
    public function def(string $property, mixed $default = null): mixed
    {
        $previous = $this->get($property, $default);

        if ($previous !== null) {
            return $previous;
        }

        return $this->set($property, $default);
    }

    /**
     * Returns a property of the object or the default value if the property is not set.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed    The value of the property.
     * @throws  \OutOfBoundsException  If the property is not public
     *
     * @since   __DEPLOY_VERSION__
     *
     * @see     CMSObject::getProperties()
     */
    public function get(string $property, mixed $default = null): mixed
    {
        if (isset($this->{$property}) && !$this->has($property)) {
            throw new \OutOfBoundsException(
                sprintf(
                    'Direct access to private and protected properties is not allowed in %s',
                    get_class($this)
                )
            );
        }

        if ($this->has($property, self::IS_DYNAMIC)) {
            return $this->joomlareserved_dynamic_properties[$property];
        }

        return $this->{$property} ?? $default;
    }

    /**
     * Modifies a property of the object, creating it if it does not already exist.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $value     The value of the property to set.
     *
     * @return  mixed  Previous value of the property, NULL if it did not exist
     * @throws  \OutOfBoundsException  If the property is not public
     *
     * @since   __DEPLOY_VERSION__
     */
    public function set(string $property, mixed $value = null): mixed
    {
        if (isset($this->{$property}) && !$this->has($property)) {
            throw new \OutOfBoundsException(
                sprintf(
                    'Direct access to private and protected properties is not allowed in %s',
                    get_class($this)
                )
            );
        }

        $previous = $this->get($property);

        if ($this->has($property, self::IS_CONCRETE)) {
            $this->{$property} = $value;
        } else {
            $this->joomlareserved_dynamic_properties[$property] = $value;
        }

        return $previous;
    }

    /**
     * Remove a dynamic property
     *
     * @param   string  $property  The name of the dynamic property to unset.
     *
     * @return  void
     * @throws  \OutOfBoundsException  If the property is not public
     * @throws  \RuntimeException  If property is concrete
     *
     * @since   __DEPLOY_VERSION__
     */
    public function remove(string $property)
    {
        if (isset($this->{$property}) && !$this->has($property)) {
            throw new \OutOfBoundsException(
                sprintf(
                    'Direct access to private and protected properties is not allowed in %s',
                    get_class($this)
                )
            );
        }

        if ($this->has($property, self::IS_CONCRETE)) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot unset concrete property %s on object of type %s',
                    $property,
                    get_class($this)
                )
            );
        }

        unset($this->joomlareserved_dynamic_properties[$property]);
    }

    /**
     * Does the object have a specific public (concrete or dynamically defined) property?
     *
     * @param   string  $property  The name of the property
     * @param   int     $mode      Property search mode
     *
     * @return  bool  TRUE if the property exists.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function has(string $property, int $mode = self::IS_ANY): bool
    {
        $publicProperties = $mode === self::IS_DYNAMIC
            ? []
            : array_filter(
                get_mangled_object_vars($this),
                fn($key) => !str_starts_with($key, "\0"),
                ARRAY_FILTER_USE_KEY
            );

        switch ($mode) {
            case self::IS_ANY:
            default:
                return array_key_exists($property, $publicProperties)
                    || array_key_exists($property, $this->joomlareserved_dynamic_properties);

            case self::IS_CONCRETE:
                return array_key_exists($property, $publicProperties);

            case self::IS_DYNAMIC:
                return array_key_exists($property, $this->joomlareserved_dynamic_properties);
        }
    }

    /**
     * Set the object properties based on a named array/hash.
     *
     * @param   array|object  $properties  Either an associative array or another object.
     *
     * @return  boolean  Always true, for b/c with CMSObject
     *
     * @since   __DEPLOY_VERSION__
     *
     * @see     self::set()
     */
    public function setProperties(array|object $properties): bool
    {
        foreach ((array)$properties as $k => $v) {
            $this->set($k, $v);
        }

        return true;
    }

    /**
     * Returns an associative array of object properties.
     *
     * @param   boolean  $public  If true, returns only the dynamic and concrete public properties.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     *
     * @see     CMSObject::get()
     */
    public function getProperties(bool $public = true): array
    {
        $vars = $public
            ? array_filter(
                get_mangled_object_vars($this),
                fn($key) => !str_starts_with($key, "\0"),
                ARRAY_FILTER_USE_KEY
            )
            : get_object_vars($this);


        return array_merge($vars, $this->joomlareserved_dynamic_properties);
    }

    /**
     * Get the most recent error message.
     *
     * @param   int|null  $i         Option error index.
     * @param   bool      $toString  Indicates if Exception objects should return their error
     *                               message.
     *
     * @return  string|false   Error message or FALSE if there is none
     *
     * @since   __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will always use exceptions
     */
    public function getError($i = null, $toString = true)
    {
        // Find the error
        if ($i === null) {
            // Default, return the last message
            $error = end($this->joomlareserved_errors);
        } elseif (!\array_key_exists($i, $this->joomlareserved_errors)) {
            // If $i has been specified but does not exist, return false
            return false;
        } else {
            $error = $this->joomlareserved_errors[$i];
        }

        // Check if only the string is requested
        if ($error instanceof \Exception && $toString) {
            return $error->getMessage();
        }

        return $error;
    }

    /**
     * Return all errors, if any.
     *
     * @return  array  Array of error messages.
     *
     * @since   __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will always use exceptions
     */
    public function getErrors()
    {
        return $this->joomlareserved_errors;
    }

    /**
     * Add an error message.
     *
     * @param   string  $error  Error message.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will always use exceptions
     */
    public function setError($error)
    {
        if ($this->joomlareserved_use_exceptions) {
            throw new \RuntimeException($error);
        }

        $this->joomlareserved_errors[] = $error;
    }

    /**
     * Set the Use Exceptions flag.
     *
     * @param   bool  $useExceptions  Should I use exceptions when setting error messages?
     *
     * @since   __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will always use exceptions
     */
    public function useExceptions(bool $useExceptions): void
    {
        $this->joomlareserved_use_exceptions = $useExceptions;
    }
}
