<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Object;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Platform Object Class
 *
 * This class allows for simple but smart objects with get and set methods and an internal error
 * handler.
 *
 * @since       1.7.0
 * @deprecated  4.0.0  Use \stdClass, \Joomla\CMS\Object\CMSDynamicObject or \Joomla\Registry\Registry instead.
 */
class CMSObject extends CMSDynamicObject
{
    /**
     * Should I throw exceptions instead of setting the error messages internally?
     *
     * Overrides the parent class' flag value to provide b/c with CMSObject.
     *
     * @var   bool
     * @since       __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will always use exceptions
     * @see CMSDynamicObject::$_use_exceptions
     */
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    protected bool $_use_exceptions = false;
    // phpcs:enable PSR2.Classes.PropertyDeclaration

    /**
     * Should underscore prefixed properties be considered private?
     *
     * Overrides the parent class' flag value to provide b/c with CMSObject.
     *
     * @var   bool
     * @since       __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will only consider member visibility
     * @see CMSDynamicObject::$_underscore_private
     */
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    protected bool $_underscore_private = true;
    // phpcs:enable PSR2.Classes.PropertyDeclaration

    /**
     * Should I allow getting and setting private properties?
     *
     * Overrides the parent class' flag value to provide b/c with CMSObject.
     *
     * @var bool
     * @since       __DEPLOY_VERSION__
     * @deprecated  7.0  Joomla 7.0 and later will disallow direct access to non-public properties
     * @see CMSDynamicObject::$_access_private
     */
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    protected bool $_access_private = true;
    // phpcs:enable PSR2.Classes.PropertyDeclaration

    /**
     * Class constructor, overridden in descendant classes.
     *
     * @param   mixed  $properties  Either and associative array or another
     *                              object to set the initial properties of the object.
     *
     * @since   1.7.0
     */
    public function __construct($properties = null)
    {
        parent::__construct($properties, true);
    }

    /**
     * Magic method to convert the object to a string gracefully.
     *
     * @return  string  The classname.
     *
     * @since   1.7.0
     * @deprecated 3.1.4  Classes should provide their own __toString() implementation.
     */
    public function __toString()
    {
        return \get_class($this);
    }
}
