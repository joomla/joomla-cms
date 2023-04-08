<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Model;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This models supports retrieving lists of article categories.
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
    public $_context = 'com_content.categories';

    /**
     * The category context (allows other extensions to derived from this model).
     *
     * @var     string
     */
    protected $_extension = 'com_content';

    /**
     * Parent category of the current one
     *
     * @var    CategoryNode|null
     */
    private $_parent = null;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   The field to order on.
     * @param   string  $direction  The direction to order on.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $this->setState('filter.extension', $this->_extension);

        // Get the parent id if defined.
        $parentId = $app->getInput()->getInt('id');
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
     * Redefine the function and add some properties to make the styling easier
     *
     * @param   bool  $recursive  True if you want to return children recursively.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getItems($recursive = false)
    {
        $store = $this->getStoreId();

        if (!isset($this->cache[$store])) {
            $app    = Factory::getApplication();
            $menu   = $app->getMenu();
            $active = $menu->getActive();

            if ($active) {
                $params = $active->getParams();
            } else {
                $params = new Registry();
            }

            $options               = [];
            $options['countItems'] = $params->get('show_cat_num_articles_cat', 1) || !$params->get('show_empty_categories_cat', 0);
            $categories            = Categories::getInstance('Content', $options);
            $this->_parent         = $categories->get($this->getState('filter.parentId', 'root'));

            if (is_object($this->_parent)) {
                $this->cache[$store] = $this->_parent->getChildren($recursive);
            } else {
                $this->cache[$store] = false;
            }
        }

        return $this->cache[$store];
    }

    /**
     * Get the parent.
     *
     * @return  object  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getParent()
    {
        if (!is_object($this->_parent)) {
            $this->getItems();
        }

        return $this->_parent;
    }
}
