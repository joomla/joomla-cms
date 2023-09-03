<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

use Joomla\CMS\Tree\NodeInterface;
use Joomla\CMS\Tree\NodeTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Object representing a menu item
 *
 * @since  3.7.0
 */
#[\AllowDynamicProperties]
class MenuItem implements NodeInterface
{
    use NodeTrait;

    /**
     * Primary key
     *
     * @var    integer
     * @since  3.7.0
     */
    public $id;

    /**
     * The type of menu this item belongs to
     *
     * @var    integer
     * @since  3.7.0
     */
    public $menutype;

    /**
     * The display title of the menu item
     *
     * @var    string
     * @since  3.7.0
     */
    public $title;

    /**
     * The SEF alias of the menu item
     *
     * @var    string
     * @since  3.7.0
     */
    public $alias;

    /**
     * A note associated with the menu item
     *
     * @var    string
     * @since  3.7.0
     */
    public $note;

    /**
     * The computed path of the menu item based on the alias field, this is populated from the `path` field in the `#__menu` table
     *
     * @var    string
     * @since  3.7.0
     */
    public $route;

    /**
     * The actual link the menu item refers to
     *
     * @var    string
     * @since  3.7.0
     */
    public $link;

    /**
     * The type of link
     *
     * @var    string
     * @since  3.7.0
     */
    public $type;

    /**
     * The relative level in the tree
     *
     * @var    integer
     * @since  3.7.0
     */
    public $level;

    /**
     * The assigned language for this item
     *
     * @var    string
     * @since  3.7.0
     */
    public $language;

    /**
     * The click behaviour of the link
     *
     * @var    integer
     * @since  3.7.0
     */
    public $browserNav;

    /**
     * The access level required to view the menu item
     *
     * @var    integer
     * @since  3.7.0
     */
    public $access;

    /**
     * The menu item parameters
     *
     * @var    string|Registry
     * @since  3.7.0
     * @note   This field is protected to require reading this field to proxy through the getter to convert the params to a Registry instance
     */
    protected $params;

    /**
     * Indicates if this menu item is the home or default page
     *
     * @var    integer
     * @since  3.7.0
     */
    public $home;

    /**
     * The image of the menu item
     *
     * @var    string
     * @since  3.7.0
     */
    public $img;

    /**
     * The optional template style applied to this menu item
     *
     * @var    integer
     * @since  3.7.0
     */
    public $template_style_id;

    /**
     * The extension ID of the component this menu item is for
     *
     * @var    integer
     * @since  3.7.0
     */
    public $component_id;

    /**
     * The parent menu item in the menu tree
     *
     * @var    integer
     * @since  3.7.0
     */
    public $parent_id;

    /**
     * The name of the component this menu item is for
     *
     * @var    string
     * @since  3.7.0
     */
    public $component;

    /**
     * The tree of parent menu items
     *
     * @var    array
     * @since  3.7.0
     */
    public $tree = [];

    /**
     * An array of the query string values for this item
     *
     * @var    array
     * @since  3.7.0
     */
    public $query = [];

    /**
     * Class constructor
     *
     * @param   array  $data  The menu item data to load
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
            try {
                $this->params = new Registry($this->params);
            } catch (\RuntimeException $e) {
                /*
                 * Joomla shipped with a broken sample json string for 4 years which caused fatals with new
                 * error checks. So for now we catch the exception here - but one day we should remove it and require
                 * valid JSON.
                 */
                $this->params = new Registry();
            }
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
