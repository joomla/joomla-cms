<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use stdClass;

use function defined;

/**
 * Methods supporting a list of Category records.
 *
 * @since  4.0.0
 */
class CategoriesModel extends ListModel
{
    public string $_context = 'com_jed.categories';
    public array $_items;
    protected string $_extension = 'com_jed';
    protected int $_total        = 0;

    /**
     * Parent category of the current one
     *
     * @var    CategoryNode|null
     *
     * @since 3.0
     */
    private CategoryNode $_parent;


    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see        JController
     *
     * @since      4.0.0
     * @throws Exception
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'search',
                'category',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Build query and where for protected _getList function and return a list
     *
     * @param   int|null  $limitStart  Where to start looking up records
     * @param   int|null  $limit       Number of records to return, set to -1 to return all records
     * @param   bool      $extended    Extend the data with links etc, default true
     *
     * @return array An array of results.
     *
     * @since 3.0
     */
    public function getItems(int $limitStart = null, int $limit = null, bool $extended = true)
    {


        if (isset($this->_items)) {
            return $this->_items;
        }
        $db    =  Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $options               = [];
        $options['extension']  = 'com_jed';
        $options['table']      = '#__jed_extensions';
        $options['countItems'] = 0;
        $options['statefield'] = 'approved';
        $options['access']     = false;
        $options['field']      = 'primary_category_id';

        $recursive  = false;
        $categories = Categories::getInstance('Jed', $options);

        $this->_parent = $categories->get($this->getState('filter.parentId', 'root'));

        if (is_object($this->_parent)) {
            $this->_items = $this->_parent->getChildren($recursive);
        } else {
            $this->_items = [];
        }

        // Get counts
        $query->select('primary_category_id, COUNT(id) AS c')
            ->from('#__jed_extensions AS e')
            ->where('e.approved = 1 and e.published=1')
            ->group('e.primary_category_id');
        $db->setQuery($query);

        $counts = $db->loadObjectList('primary_category_id');

        $null    = new stdClass();
        $null->c = 0;
        $list    = [];

        $this->_total = 0;

        foreach ($this->_items as $item) {
            $row           = $this->nodeToObject($item);
            $row->numitems = ArrayHelper::getValue($counts, $row->id, $null)->c;
            $children      = $item->getChildren();
            $parentCount   = 0;

            foreach ($children as $child) {
                $i                        = $this->nodeToObject($child);
                $i->numitems              = ArrayHelper::getValue($counts, $i->id, $null)->c;
                $row->children[$i->title] = $i;
                $parentCount += $i->numitems;
                $this->_total += $i->numitems;
            }

            ksort($row->children);

            $row->numitems     = $row->numitems + $parentCount;
            $list[$row->title] = $row;
        }

        ksort($list, SORT_NATURAL | SORT_FLAG_CASE);
        $list = array_values($list);


        $list = array_values($list);
        array_multisort(array_column($list, "numitems"), SORT_DESC, $list);
        //echo "<pre>";print_r($list);echo "</pre>";exit();
        $this->_items = $list;

        return $list;
    }

    /**
     * Get the parent.
     *
     * @return  object  An array of data items on success, false on failure.
     *
     * @since   3.0
     */
    public function getParent()
    {
        if (!is_object($this->_parent)) {
            $this->getItems();
        }

        return $this->_parent;
    }

    /**
     * Get total number of rows for pagination
     *
     * @return  int  Total number of records
     *
     * @since 3.0
     */
    public function getTotal(): int
    {


        if (empty($this->_total)) {
            $this->_total = count($this->_items());
        }

        return $this->_total;
    }

    /**
     * Convert an node to an object
     *
     * @param   object  $item  XML node
     *
     * @return stdClass
     *
     * @since 3.0
     */
    private function nodeToObject(object $item): stdClass
    {
        $row            = new stdClass();
        $row->extension = $item->extension;
        $row->title     = $item->title;
        $row->alias     = $item->alias;
        $row->slug      = $item->slug;
        $row->parent_id = $item->parent_id;
        $row->id        = $item->id;
        $row->children  = [];

        return $row;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Elements order
     * @param   string  $direction  Order direction
     *
     * @return void
     *
     * @since    1.6
     * @throws Exception
     *
     */
    protected function populateState($ordering = null, $direction = null)
    {
        if ($ordering === null) {
            $ordering = 'categories.ordering';
        }

        if ($direction === null) {
            $direction = 'ASC';
        }

        parent::populateState($ordering, $direction);
    }
}
