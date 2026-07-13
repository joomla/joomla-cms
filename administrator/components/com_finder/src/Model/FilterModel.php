<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Component\Finder\Administrator\Table\FilterTable;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Filter model class for Finder.
 *
 * @since  2.5
 */
class FilterModel extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  2.5
     */
    protected $text_prefix = 'COM_FINDER';

    /**
     * Model context string.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'com_finder.filter';

    /**
     * Custom clean cache method.
     *
     * @param   string  $group  The component name. [optional]
     *
     * @return  void
     *
     * @since   2.5
     */
    protected function cleanCache($group = 'com_finder')
    {
        parent::cleanCache($group);
    }

    /**
     * Method to get the filter data.
     *
     * @return  FilterTable|boolean  The filter data or false on a failure.
     *
     * @since   2.5
     */
    public function getFilter()
    {
        $filter_id = (int) $this->getState('filter.id');

        // Get a FinderTableFilter instance.
        $filter = $this->getTable();

        // Attempt to load the row.
        $return = $filter->load($filter_id);

        // Check for a database error.
        if ($return === false && $filter->getError()) {
            $this->setError($filter->getError());

            return false;
        }

        // Process the filter data.
        if (!empty($filter->data)) {
            $filter->data = explode(',', $filter->data);
        } elseif (empty($filter->data)) {
            $filter->data = [];
        }

        return $filter;
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form. [optional]
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
     *
     * @return  Form  A Form object
     *
     * @since   2.5
     * @throws  \Exception on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm('com_finder.filter', 'filter', ['control' => 'jform', 'load_data' => $loadData]);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   2.5
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_finder.edit.filter.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_finder.filter', $data);

        return $data;
    }

    /**
     * Method to get the total indexed items
     *
     * @return  integer  The count of indexed items
     *
     * @since  3.5
     */
    public function getTotal()
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select('MAX(link_id)')
            ->from('#__finder_links');

        return $db->setQuery($query)->loadResult();
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function save($data)
    {
        $params = new Registry($data['params'] ?? []);
        $params->set('active_user_only', Factory::getApplication()->getInput()->getInt('active_user_only', 0));

        $data['params'] = $params->toArray();

        if ((int) $params->get('active_user_only') === 1) {
            $data['data'] = array_diff($data['data'] ?? [], $this->getAuthorTaxonomyIds());
        }

        return parent::save($data);
    }

    /**
     * Get all author taxonomy node ids.
     *
     * @return  integer[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getAuthorTaxonomyIds(): array
    {
        $db    = $this->getDatabase();
        $author = 'Author';
        $query = $db->getQuery(true)
            ->select($db->quoteName('t.id'))
            ->from($db->quoteName('#__finder_taxonomy', 't'))
            ->innerJoin(
                $db->quoteName('#__finder_taxonomy', 'p'),
                $db->quoteName('p.id') . ' = ' . $db->quoteName('t.parent_id')
            )
            ->where($db->quoteName('p.title') . ' = :branch')
            ->bind(':branch', $author, ParameterType::STRING);

        return array_map('intval', $db->setQuery($query)->loadColumn());
    }

}
