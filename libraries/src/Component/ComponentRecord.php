<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component;

use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Object representing a component extension record
 *
 * @since  3.7.0
 */
class ComponentRecord
{
    /**
     * Primary key
     *
     * @var    integer
     * @since  3.7.0
     */
    public $id;

    /**
     * The component name
     *
     * @var    integer
     * @since  3.7.0
     */
    public $option;

    /**
     * The component parameters
     *
     * @var    string|Registry
     * @since  3.7.0
     * @note   This field is protected to require reading this field to proxy through the getter to convert the params to a Registry instance
     */
    protected $params;

    /**
     * The extension namespace
     *
     * @var    string
     * @since  4.0.0
     */
    public $namespace;

    /**
     * Indicates if this component is enabled
     *
     * @var    integer
     * @since  3.7.0
     */
    public $enabled;

    /**
     * Class constructor
     *
     * @param   array  $data  The component record data to load
     *
     * @since   3.7.0
     */
    public function __construct($data = [])
    {
        foreach ((array) $data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.7.0
     * @deprecated  5.0  Access the item parameters through the `getParams()` method
     */
    public function __get($name)
    {
        if ($name === 'params') {
            return $this->getParams();
        }

        return $this->$name;
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.7.0
     * @deprecated  5.0  Set the item parameters through the `setParams()` method
     */
    public function __set($name, $value)
    {
        if ($name === 'params') {
            $this->setParams($value);

            return;
        }

        $this->$name = $value;
    }

    /**
     * Returns the menu item parameters
     *
     * @return  Registry
     *
     * @since   3.7.0
     */
    public function getParams()
    {
        if (!($this->params instanceof Registry)) {
            $this->params = new Registry($this->params);
        }

        return $this->params;
    }

    /**
     * Sets the menu item parameters
     *
     * @param   Registry|string  $params  The data to be stored as the parameters
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
}
