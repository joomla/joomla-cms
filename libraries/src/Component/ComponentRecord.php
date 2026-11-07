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
\defined('_JEXEC') or die;
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
     * Indicates if this component is enabled
     *
     * @var    integer
     * @since  3.7.0
     */
    public $enabled;

    /**
     * The component custom data
     *
     * @var    string
     * @since  6.1.0
     */
    public $custom_data;

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
