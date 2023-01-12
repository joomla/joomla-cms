<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Site\Model;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This models supports retrieving lists of newsfeed categories.
 *
 * @since  1.6
 */
class CategoriesModel extends ListModel
{
    /**
     * Model context string.
     *
     * @var     string
     */
    public $_context = 'com_newsfeeds.categories';

    /**
     * The category context (allows other extensions to derived from this model).
     *
     * @var     string
     */
    protected $_extension = 'com_newsfeeds';

    /**
     * Parent category of the current one
     *
     * @var    CategoryNode|null
     */
    private $_parent = null;

    /**
     * Array of child-categories
     *
     * @var    CategoryNode[]|null
     */
    private $_items = null;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field
     * @param   string  $direction  An optional direction [asc|desc]
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $this->setState('filter.extension', $this->_extension);

        // Get the parent id if defined.
        $parentId = $app->input->getInt('id');
        $this->setState('filter.parentId', $parentId);

        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('filter.published', 1);
        $this->setState('filter.access', true);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.parentId');

        return parent::getStoreId($id);
    }

    /**
     * redefine the function and add some properties to make the styling easier
     *
     * @return mixed An array of data items on success, false on failure.
     */
    public function getItems()
    {
        if ($this->_items === null) {
            $app = Factory::getApplication();
            $menu = $app->getMenu();
            $active = $menu->getActive();

            if ($active) {
                $params = $active->getParams();
            } else {
                $params = new Registry();
            }

            $options = [];
            $options['countItems'] = $params->get('show_cat_items_cat', 1) || !$params->get('show_empty_categories_cat', 0);
            $categories = Categories::getInstance('Newsfeeds', $options);
            $this->_parent = $categories->get($this->getState('filter.parentId', 'root'));

            if (is_object($this->_parent)) {
                $this->_items = $this->_parent->getChildren();
            } else {
                $this->_items = false;
            }
        }

        return $this->_items;
    }

    /**
     * get the Parent
     *
     * @return null
     */
    public function getParent()
    {
        if (!is_object($this->_parent)) {
            $this->getItems();
        }

        return $this->_parent;
    }
}
