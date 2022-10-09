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
 * - Using the underscore (`_`) prefix for "private" properties is not allowed. Use property
 *   visibility instead. Use joomlareserved_underscore_private to override.
 * - getProperties(true) will return dynamic properties with underscore prefixes as they are no
 *   longer considered "private" (see above).
 * - You cannot modify concrete non-public properties. Use joomlareserved_access_private to
 *   override.
 * - Property names MUST NOT have the prefix `joomlareserved_`. This is a reserved prefix for
 *   internal use. This requirement is not enforced but if you use this prefix in your properties
 *   your objects might break in the future.
 * - Using setError throws a RuntimeException. Set joomlareserved_use_exceptions to false to go back
 *   to the error messages stack behavior of CMSObject.
 *
 * All aforementioned flags can be set using $this->setCMSObjectBackwardsCompatibility(true).
 *
 * All flags and the error management are deprecated in Joomla 5.0 and will be removed in Joomla
 * 7.0.
 *
 * CAVEATS USING CMSObject B/C MODE:
 * - You cannot assign to dynamic properties by reference, e.g. `$o->a = &$something` will fail. In
 *   most cases you do not really need to do that. If you need to do that, use a subclass of
 *   CMSDynamicObject with concrete properties which will receive the by-reference values.
 * - If you override any magic method in your subclasses you need to fall back to the default
 *   implementation of the parent class (CMSDynamicObject) to prevent errors.
 * - The __get magic method returns **BY REFERENCE**. This is necessary for you to be able to modify
 *   arrays in dynamic properties e.g. `$o->a[1] = 123`. As a result, if you override the __get
 *   magic method you must also make it return by reference in your own code.
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
    // phpcs:disable
    protected array $_errors = [];
    // phpcs:enable

    /**
     * Should I throw exceptions instead of setting the error messages internally?
     *
     * When enabled (default) setError will immediately throw a RuntimeException. The getErrors
     * method always returns an empty array, and the getError method always returns boolean FALSE.
     *
     * When disabled, setError pushes the messages into an array. The getErrors method returns the
     * contents of the errors array, and the getError method return the latest error message, or
     * boolean FALSE when the errors array is empty. This is how CMSObject used to work.
     *
     * @var   bool
     * @since __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will always use exceptions
     */
    protected bool $joomlareserved_use_exceptions = true;

    /**
     * Should underscore prefixed properties be considered private?
     *
     * When disabled (default) only concrete properties' visibility is taken into account. You can
     * only modify public concrete properties and all dynamic properties, regardless of their
     * name.
     *
     * When enabled, only properties whose name is prefixed with an underscore or `joomlareserved_`
     * are considered private. Everything else is considered public and becomes user-accessible,
     * regardless of the visibility of a concrete property by that name. This is how CMSObject used
     * to work.
     *
     * @var   bool
     * @since __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will only consider member visibility
     */
    protected bool $joomlareserved_underscore_private = false;

    /**
     * Should I allow getting and setting private properties?
     *
     * When disabled (default) you cannot get or set private properties.
     *
     * When enabled, you can get and set private properties, even if they are concrete properties
     * with a protected or private visibility.
     *
     * @var bool
     * @since __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will disallow direct access to non-public properties
     */
    protected bool $joomlareserved_access_private = false;

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
    public function __construct($properties = null, bool $cmsObjectCompatibility = false)
    {
        $this->setCMSObjectBackwardsCompatibility($cmsObjectCompatibility);

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
    public function &__get(string $name)
    {
        if (!$this->joomlareserved_access_private && isset($this->{$name}) && !$this->has($name)) {
            throw new \OutOfBoundsException(
                sprintf(
                    'Direct access to private and protected properties is not allowed in %s',
                    get_class($this)
                )
            );
        }

        if ($this->has($name, self::IS_CONCRETE)) {
            return $this->{$name};
        }

        if (!$this->has($name, self::IS_DYNAMIC)) {
            $this->joomlareserved_dynamic_properties[$name] = null;
        }

        return $this->joomlareserved_dynamic_properties[$name];
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
    public function def($property, $default = null)
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
    public function get($property, $default = null)
    {
        if (!$this->joomlareserved_access_private && isset($this->{$property}) && !$this->has($property)) {
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
    public function set($property, $value = null)
    {
        if (!$this->joomlareserved_access_private && isset($this->{$property}) && !$this->has($property)) {
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
        if (!$this->joomlareserved_access_private && isset($this->{$property}) && !$this->has($property)) {
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
     * Get the concrete properties which are considered "public" (user-accessible).
     *
     * The behavior of this method depends on the $this->joomlareserved_underscore_private flag.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     * @deprecated 7.0
     */
    private function getConcretePublicProperties(): array
    {
        if ($this->joomlareserved_underscore_private) {
            return array_filter(
                get_object_vars($this),
                fn($key) => !str_starts_with($key, '_') && !str_starts_with($key, 'joomlareserved_'),
                ARRAY_FILTER_USE_KEY
            );
        }

        return array_filter(
            get_mangled_object_vars($this),
            fn($key) => !str_starts_with($key, "\0"),
            ARRAY_FILTER_USE_KEY
        );
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
            : (
                $this->joomlareserved_access_private
                    ? get_object_vars($this)
                    : $this->getConcretePublicProperties()
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
     * When $this->joomlareserved_use_exceptions is false (default) the return value is always true.
     * If you pass a parameter which is neither an array nor an object you will get a TypeError
     * exception.
     *
     * When $this->joomlareserved_use_exceptions is true (CMSObject b/c mode) the return value is
     * true, unless you pass a parameter which is neither an array nor an object in which case you
     * get false.
     *
     * @param   array|object  $properties  Either an associative array or another object.
     *
     * @return  boolean  True on success, false when $properties is neither an array nor an object.
     *
     * @since   __DEPLOY_VERSION__
     *
     * @see     self::set()
     */
    public function setProperties($properties)
    {
        if (!is_array($properties) && !is_object($properties)) {
            if ($this->joomlareserved_use_exceptions) {
                throw new \TypeError(
                    sprintf(
                        'The parameter to %s must be an array or an object, %s given',
                        __METHOD__,
                        get_debug_type($properties)
                    )
                );
            }

            return false;
        }

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
    public function getProperties($public = true)
    {
        $vars = $public
            ? $this->getConcretePublicProperties()
            : get_object_vars($this);

        $vars = array_merge($vars, $this->joomlareserved_dynamic_properties);

        if ($this->joomlareserved_underscore_private && $public) {
            $vars = array_filter(
                $vars,
                fn($key) => !str_starts_with($key, '_') && !str_starts_with($key, 'joomlareserved_'),
                ARRAY_FILTER_USE_KEY
            );
        }

        return $vars;
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
        if ($this->joomlareserved_use_exceptions) {
            return false;
        }

        // Find the error
        if ($i === null) {
            // Default, return the last message
            $error = end($this->_errors);
        } elseif (!\array_key_exists($i, $this->_errors)) {
            // If $i has been specified but does not exist, return false
            return false;
        } else {
            $error = $this->_errors[$i];
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
        if ($this->joomlareserved_use_exceptions) {
            return [];
        }

        return $this->_errors;
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

        $this->_errors[] = $error;
    }

    /**
     * Should I enable the backwards compatibility with CMSObject?
     *
     * When this is enabled properties with an underscore prefix are considered 'private'. Moreover,
     * get() and set() allow you to access the values of these pseudo-'private' properties, be they
     * concrete or dynamic.
     *
     * Furthermore, the legacy error handling is used instead of exceptions.
     *
     * @param   bool  $enableCompatibilty  Enable backwards compatibility with CMSObject?
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @deprecated  7.0
     */
    protected function setCMSObjectBackwardsCompatibility(bool $enableCompatibilty): void
    {
        $this->joomlareserved_underscore_private = $enableCompatibilty;
        $this->joomlareserved_access_private     = $enableCompatibilty;
        $this->joomlareserved_use_exceptions     = !$enableCompatibilty;
    }
}
