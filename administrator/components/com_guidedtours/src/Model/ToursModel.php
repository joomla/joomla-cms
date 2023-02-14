<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Guidedtours\Administrator\Helper\GuidedtoursHelper;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Tours
 *
 * @since  __DEPLOY_VERSION__
 */
class ToursModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'access', 'access_level', 'a.access',
                'description', 'a.description',
                'published', 'a.published',
                'language', 'a.language',
                'ordering', 'a.ordering',
                'extensions', 'a.extensions',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'modified_by', 'a.modified_by',
            );
        }

        parent::__construct($config);
    }

   /**
    * Provide a query to be used to evaluate if this is an Empty State, can be overridden in the model to provide granular control.
    *
    * @return DatabaseQuery
    *
    * @since 4.0.0
    */
    protected function getEmptyStateQuery()
    {
        $query = clone $this->_getListQuery();

        if ($query instanceof DatabaseQuery) {
            $query->clear('bounded')
                ->clear('group')
                ->clear('having')
                ->clear('join')
                ->clear('values')
                ->clear('where');

            // override of ListModel to keep the tour id filter
            $db = $this->getDbo();
            $tour_id = $this->getState('filter.tour_id');
            if ($tour_id) {
                $tour_id = (int) $tour_id;
                $query->where($db->quoteName('a.tour_id') . ' = :tour_id')
                    ->bind(':tour_id', $tour_id, ParameterType::INTEGER);
            }
        }

        return $query;
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        $app = Factory::getApplication();
        $extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', null, 'cmd');

        $this->setState('filter.extension', $extension);

        // Extract the optional section name

        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\Table\Table  A JTable object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTable($type = 'Tour', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }
    /**
     * Get the filter form
     *
     * @param   array    $data      data
     * @param   boolean  $loadData  load current data
     *
     * @return  \JForm|false  the JForm object or false
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);

        if ($form) {
            $form->setValue('extension', null, $this->getState('filter.extension'));
        }

        return $form;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  string  The query to database.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*, (SELECT COUNT(' . $db->quoteName('description') . ') FROM '
                . $db->quoteName('#__guidedtour_steps')
                . ' WHERE ' . $db->quoteName('tour_id') . ' = ' . $db->quoteName('a.id')
                . ' AND ' . $db->quoteName('published') . ' = 1'
                . ') AS ' . $db->quoteName('steps_count')
            )
        );
        $query->from('#__guidedtours AS a');

        // Join with language table
        $query->select(
            [
                $db->quoteName('l.title', 'language_title'),
                $db->quoteName('l.image', 'language_image'),
            ]
        )
            ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

        // Filter by extension
        if ($extension = $this->getState('filter.extension')) {
            $query->where($db->quoteName('extension') . ' = :extension')
                ->bind(':extension', $extension);
        }

        $status = (string) $this->getState('filter.published');

        // Filter by status
        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $status, ParameterType::INTEGER);
        } elseif ($status === '') {
            $query->where($db->quoteName('a.published') . ' IN (0, 1)');
        }

        // Filter by access level.
        if ($access = (int) $this->getState('filter.access')) {
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } elseif (stripos($search, 'description:') === 0) {
                $search = '%' . substr($search, 8) . '%';
                $query->where('(' . $db->quoteName('a.description') . ' LIKE :search1)')
                    ->bind([':search1'], $search);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('a.title') . ' LIKE :search1 OR ' . $db->quoteName('a.id') . ' LIKE :search2'
                    . ' OR ' . $db->quoteName('a.description') . ' LIKE :search3)'
                )
                    ->bind([':search1', ':search2', ':search3'], $search);
            }
        }

        // Filter by extensions in Component
        $extensions = $this->getState('list.extensions');

        if (!empty($extensions)) {
            $extensions = '%' . $extensions . '%';
            $all = '%*%';
            $query->where(
                '(' . $db->quoteName('a.extensions') . ' LIKE :all  OR ' .
                $db->quoteName('a.extensions') . ' LIKE :extensions)'
            )
                ->bind([':all'], $all)
                ->bind([':extensions'], $extensions);
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.ordering');
        $orderDirn = strtoupper($this->state->get('list.direction', 'ASC'));

        $query->order($db->escape($orderCol) . ' ' . ($orderDirn === 'DESC' ? 'DESC' : 'ASC'));

        return $query;
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItems()
    {
        $items = parent::getItems();

        $lang = Factory::getLanguage();
        $lang->load('com_guidedtours.sys', JPATH_ADMINISTRATOR);

        if ($items != false) {
            foreach ($items as $item) {
                $item->title = Text::_($item->title);
                $item->description = Text::_($item->description);
            }
        }

        return $items;
    }
}
