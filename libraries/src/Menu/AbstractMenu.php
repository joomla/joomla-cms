<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu class
 *
 * @since  1.5
 */
abstract class AbstractMenu
{
    /**
     * Array to hold the menu items
     *
     * @var    MenuItem[]
     *
     * @since  4.0.0
     */
    protected $items = [];

    /**
     * Identifier of the default menu item. Key of the array is the language.
     *
     * @var    integer[]
     *
     * @since  4.0.0
     */
    protected $default = [];

    /**
     * Identifier of the active menu item
     *
     * @var    integer
     *
     * @since  4.0.0
     */
    protected $active = 0;

    /**
     * Menu instances container.
     *
     * @var    AbstractMenu[]
     *
     * @since  1.7
     *
     * @deprecated 5.0 Use the MenuFactoryInterface from the container instead
     */
    public static $instances = [];

    /**
     * User object to check access levels for
     *
     * @var    User
     *
     * @since  3.9.26
     */
    protected $storedUser;

    /**
     * Flag for checking if the menu items have been loaded
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    private $itemsLoaded = false;

    /**
     * Class constructor
     *
     * @param   array  $options  An array of configuration options.
     *
     * @since   1.5
     */
    public function __construct($options = [])
    {
        /**
         * It is preferred NOT to inject and store the user when constructing the menu object,
         * at least for the Menu object used by Joomla.
         * The menu object can be built very early in the request, from an onAfterInitialise event
         * but the user can be updated later (by the Remember me plugin for instance). As the stored
         * user object is not updated, the menu will render incorrectly, not complying with
         * menu items access levels.
         *
         * @see https://github.com/joomla/joomla-cms/issues/11541
         */
        $this->storedUser = isset($options['user']) && $options['user'] instanceof User ? $options['user'] : null;
    }

    /**
     * Returns a Menu object
     *
     * @param   string  $client   The name of the client
     * @param   array   $options  An associative array of options
     *
     * @return  AbstractMenu  A menu object.
     *
     * @since       1.5
     *
     * @throws      \Exception
     *
     * @deprecated  5.0 Use the MenuFactoryInterface from the container instead
     */
    public static function getInstance($client, $options = [])
    {
        if (!$client) {
            throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_MENU_LOAD', $client), 500);
        }

        if (empty(self::$instances[$client])) {
            self::$instances[$client] = Factory::getContainer()->get(MenuFactoryInterface::class)->createMenu($client, $options);
        }

        return self::$instances[$client];
    }

    /**
     * Setter for the current user used to build menu.
     *
     * @param   User  $user  The new user to set.
     *
     * @return  void
     *
     * @since 3.9.26
     */
    public function setUser($user)
    {
        $this->storedUser = $user;
    }

    /**
     * Get menu item by id
     *
     * @param   integer  $id  The item id
     *
     * @return  MenuItem|null  The item object if the ID exists or null if not found
     *
     * @since   1.5
     */
    public function getItem($id)
    {
        $result = null;

        if (isset($this->getMenu()[$id])) {
            $result = &$this->getMenu()[$id];
        }

        return $result;
    }

    /**
     * Set the default item by id and language code.
     *
     * @param   integer  $id        The menu item id.
     * @param   string   $language  The language code (since 1.6).
     *
     * @return  boolean  True if a menu item with the given ID exists
     *
     * @since   1.5
     */
    public function setDefault($id, $language = '*')
    {
        if (isset($this->getMenu()[$id])) {
            $this->default[$language] = $id;

            return true;
        }

        return false;
    }

    /**
     * Get the default item by language code.
     *
     * @param   string  $language  The language code, default value of * means all.
     *
     * @return  MenuItem|null  The item object or null when not found for given language
     *
     * @since   1.5
     */
    public function getDefault($language = '*')
    {
        // Get menu items first to ensure defaults have been populated
        $items = $this->getMenu();

        if (\array_key_exists($language, $this->default)) {
            return $items[$this->default[$language]];
        }

        if (\array_key_exists('*', $this->default)) {
            return $items[$this->default['*']];
        }

        return null;
    }

    /**
     * Set the default item by id
     *
     * @param   integer  $id  The item id
     *
     * @return  MenuItem|null  The menu item representing the given ID if present or null otherwise
     *
     * @since   1.5
     */
    public function setActive($id)
    {
        if (isset($this->getMenu()[$id])) {
            $this->active = $id;

            return $this->getMenu()[$id];
        }

        return null;
    }

    /**
     * Get menu item by id.
     *
     * @return  MenuItem|null  The item object if an active menu item has been set or null
     *
     * @since   1.5
     */
    public function getActive()
    {
        if ($this->active) {
            return $this->getMenu()[$this->active];
        }

        return null;
    }

    /**
     * Gets menu items by attribute
     *
     * @param   mixed    $attributes  The field name(s).
     * @param   mixed    $values      The value(s) of the field. If an array, need to match field names
     *                                each attribute may have multiple values to lookup for.
     * @param   boolean  $firstonly   If true, only returns the first item found
     *
     * @return  MenuItem|MenuItem[]  An array of menu item objects or a single object if the $firstonly parameter is true
     *
     * @since   1.5
     */
    public function getItems($attributes, $values, $firstonly = false)
    {
        $items      = [];
        $attributes = (array) $attributes;
        $values     = (array) $values;
        $count      = \count($attributes);

        foreach ($this->getMenu() as $item) {
            if (!\is_object($item)) {
                continue;
            }

            $test = true;

            for ($i = 0; $i < $count; $i++) {
                if (\is_array($values[$i])) {
                    if (!\in_array($item->{$attributes[$i]}, $values[$i])) {
                        $test = false;
                        break;
                    }
                } else {
                    if ($item->{$attributes[$i]} != $values[$i]) {
                        $test = false;
                        break;
                    }
                }
            }

            if ($test) {
                if ($firstonly) {
                    return $item;
                }

                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Gets the parameter object for a certain menu item
     *
     * @param   integer  $id  The item id
     *
     * @return  Registry
     *
     * @since   1.5
     */
    public function getParams($id)
    {
        if ($menu = $this->getItem($id)) {
            return $menu->getParams();
        }

        return new Registry();
    }

    /**
     * Getter for the menu array
     *
     * @return  MenuItem[]
     *
     * @since   1.5
     */
    public function getMenu()
    {
        if (!$this->itemsLoaded) {
            $this->load();

            foreach ($this->items as $item) {
                if ($item->home) {
                    $this->default[trim($item->language)] = $item->id;
                }
            }

            $this->itemsLoaded = true;
        }

        return $this->items;
    }

    /**
     * Method to check Menu object authorization against an access control object and optionally an access extension object
     *
     * @param   integer  $id  The menu id
     *
     * @return  boolean
     *
     * @since   1.5
     */
    public function authorise($id)
    {
        $menu = $this->getItem($id);

        if ($menu) {
            $access = (int) $menu->access;

            // If the access level is public we don't need to load the user session
            if ($access === 1) {
                return true;
            }

            return \in_array($access, $this->user->getAuthorisedViewLevels(), true);
        }

        return true;
    }

    /**
     * Loads the menu items
     *
     * @return  array
     *
     * @since   1.5
     */
    abstract public function load();

    /**
     * Internal getter for the user. Returns the injected
     * one if any, or the current one if none.
     *
     * @return User
     *
     * @since 3.9.26
     */
    protected function getUser()
    {
        return empty($this->storedUser)
            ? Factory::getUser()
            : $this->storedUser;
    }

    /**
     * Magic getter for the user object. Returns the injected
     * one if any, or the current one if none.
     *
     * Using a magic getter to preserve B/C when we stopped storing the user object upon construction of the menu object.
     * As the user property is not initialized anymore, this getter ensures any class extending
     * this one can still use $instance->user and get a proper value.
     *
     * @param   string  $propName  Name of the missing or protected property.
     *
     * @return User|null
     *
     * @since 3.9.26
     */
    public function __get($propName)
    {
        if ($propName === 'user') {
            return empty($this->storedUser)
                ? Factory::getUser()
                : $this->storedUser;
        }

        return null;
    }
}
