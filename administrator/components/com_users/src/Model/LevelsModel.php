<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\ViewLevel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of user access level records.
 *
 * @since  1.6
 */
class LevelsModel extends ListModel
{
    /**
     * Override parent constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'ordering', 'a.ordering',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        // Load the parameters.
        $params = ComponentHelper::getParams('com_users');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
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
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  QueryInterface
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from($db->quoteName('#__viewlevels') . ' AS a');

        // Add the level in the tree.
        $query->group('a.id, a.title, a.ordering, a.rules');

        // Filter the items over the search string if set.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id');
                $query->bind(':id', $ids, ParameterType::INTEGER);
            } else {
                $search = '%' . trim($search) . '%';
                $query->where('a.title LIKE :title')
                    ->bind(':title', $search);
            }
        }

        $query->group('a.id');

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

    /**
     * Method to adjust the ordering of a row.
     *
     * @param   integer  $pk         The ID of the primary key to move.
     * @param   integer  $direction  Increment, usually +1 or -1
     *
     * @return  boolean  False on failure or error, true otherwise.
     */
    public function reorder($pk, $direction = 0)
    {
        // Sanitize the id and adjustment.
        $pk   = (!empty($pk)) ? $pk : (int) $this->getState('level.id');
        $user = $this->getCurrentUser();

        // Get an instance of the record's table.
        $table = new ViewLevel($this->getDatabase());

        // Load the row.
        if (!$table->load($pk)) {
            $this->setError($table->getError());

            return false;
        }

        // Access checks.
        $allow = $user->authorise('core.edit.state', 'com_users');

        if (!$allow) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));

            return false;
        }

        // Move the row.
        // @todo: Where clause to restrict category.
        $table->move($pk);

        return true;
    }

    /**
     * Saves the manually set order of records.
     *
     * @param   array    $pks    An array of primary key ids.
     * @param   integer  $order  Order position
     *
     * @return  boolean  Boolean true on success, boolean false
     *
     * @throws  \Exception
     */
    public function saveorder($pks, $order)
    {
        $table      = new ViewLevel($this->getDatabase());
        $user       = $this->getCurrentUser();
        $conditions = [];

        if (empty($pks)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_ERROR_LEVELS_NOLEVELS_SELECTED'), 'error');

            return false;
        }

        // Update ordering values
        foreach ($pks as $i => $pk) {
            $table->load((int) $pk);

            // Access checks.
            $allow = $user->authorise('core.edit.state', 'com_users');

            if (!$allow) {
                // Prune items that you can't change.
                unset($pks[$i]);
                Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
            } elseif ($table->ordering != $order[$i]) {
                $table->ordering = $order[$i];

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            }
        }

        // Execute reorder for each category.
        foreach ($conditions as $cond) {
            $table->load($cond[0]);
            $table->reorder($cond[1]);
        }

        return true;
    }
}
