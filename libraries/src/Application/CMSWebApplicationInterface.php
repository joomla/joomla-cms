<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

use Joomla\Application\SessionAwareWebApplicationInterface;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Router\Router;

/**
 * Interface defining a Joomla! CMS Application class for web applications.
 *
 * @since  4.0.0
 */
interface CMSWebApplicationInterface extends SessionAwareWebApplicationInterface, CMSApplicationInterface
{
    /**
     * Method to get the application document object.
     *
     * @return  Document  The document object
     *
     * @since   4.0.0
     */
    public function getDocument();

    /**
     * Get the menu object.
     *
     * @param   string  $name     The application name for the menu
     * @param   array   $options  An array of options to initialise the menu with
     *
     * @return  AbstractMenu|null  An AbstractMenu object or null if not set.
     *
     * @since   4.0.0
     */
    public function getMenu($name = null, $options = array());

    /**
     * Returns the application Router object.
     *
     * @param   string  $name     The name of the application.
     * @param   array   $options  An optional associative array of configuration settings.
     *
     * @return  Router
     *
     * @since      4.0.0
     *
     * @deprecated 5.0 Inject the router or load it from the dependency injection container
     */
    public static function getRouter($name = null, array $options = array());

    /**
     * Gets a user state.
     *
     * @param   string  $key      The path of the state.
     * @param   mixed   $default  Optional default value, returned if the internal value is null.
     *
     * @return  mixed  The user state or null.
     *
     * @since   4.0.0
     */
    public function getUserState($key, $default = null);

    /**
     * Gets the value of a user state variable.
     *
     * @param   string  $key      The key of the user state variable.
     * @param   string  $request  The name of the variable passed in a request.
     * @param   string  $default  The default value for the variable if not found. Optional.
     * @param   string  $type     Filter for the variable, for valid values see {@link InputFilter::clean()}. Optional.
     *
     * @return  mixed  The request user state.
     *
     * @since   4.0.0
     */
    public function getUserStateFromRequest($key, $request, $default = null, $type = 'none');

    /**
     * Sets the value of a user state variable.
     *
     * @param   string  $key    The path of the state.
     * @param   mixed   $value  The value of the variable.
     *
     * @return  mixed|void  The previous state, if one existed. Void otherwise.
     *
     * @since   4.0.0
     */
    public function setUserState($key, $value);
}
