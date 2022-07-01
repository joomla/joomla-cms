<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Table;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

/**
 * Workflow table
 *
 * @since  4.0.0
 */
class WorkflowTable extends Table
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since  4.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = '{extension}.workflow';

        parent::__construct('#__workflows', 'id', $db);
    }

    /**
     * Deletes workflow with transition and states.
     *
     * @param   int  $pk  Extension ids to delete.
     *
     * @return  boolean
     *
     * @since  4.0.0
     *
     * @throws  \Exception on ACL error
     */
    public function delete($pk = null)
    {
        $db  = $this->getDbo();
        $app = Factory::getApplication();
        $pk  = (int) $pk;

        // Gets the workflow information that is going to be deleted.
        $query = $db->getQuery(true)
            ->select($db->quoteName('default'))
            ->from($db->quoteName('#__workflows'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $pk, ParameterType::INTEGER);

        $isDefault = $db->setQuery($query)->loadResult();

        if ($isDefault) {
            $app->enqueueMessage(Text::_('COM_WORKFLOW_MSG_DELETE_DEFAULT'), 'error');

            return false;
        }

        // Delete the workflow states, then transitions from all tables.
        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__workflow_stages'))
                ->where($db->quoteName('workflow_id') . ' = :id')
                ->bind(':id', $pk, ParameterType::INTEGER);

            $db->setQuery($query)->execute();

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__workflow_transitions'))
                ->where($db->quoteName('workflow_id') . ' = :id')
                ->bind(':id', $pk, ParameterType::INTEGER);

            $db->setQuery($query)->execute();

            return parent::delete($pk);
        } catch (\RuntimeException $e) {
            $app->enqueueMessage(Text::sprintf('COM_WORKFLOW_MSG_WORKFLOWS_DELETE_ERROR', $e->getMessage()), 'error');

            return false;
        }
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True on success
     *
     * @see     Table::check()
     * @since   4.0.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (trim($this->title) === '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_WORKFLOW'));

            return false;
        }

        if (!empty($this->default)) {
            if ((int) $this->published !== 1) {
                $this->setError(Text::_('COM_WORKFLOW_ITEM_MUST_PUBLISHED'));

                return false;
            }
        } else {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__workflows'))
                ->where($db->quoteName('default') . ' = 1');

            $id = $db->setQuery($query)->loadResult();

            // If there is no default workflow => set the current to default to recover
            if (empty($id)) {
                $this->default = '1';
            } elseif ($id === $this->id) {
                // This workflow is the default, but someone has tried to disable it => not allowed
                $this->setError(Text::_('COM_WORKFLOW_DISABLE_DEFAULT'));

                return false;
            }
        }

        return true;
    }

    /**
     * Overloaded store function
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  mixed  False on failure, positive integer on success.
     *
     * @see     Table::store()
     * @since   4.0.0
     */
    public function store($updateNulls = true)
    {
        $date = Factory::getDate();
        $user = Factory::getUser();

        $table = new WorkflowTable($this->getDbo());

        if ($this->id) {
            // Existing item
            $this->modified_by = $user->id;
            $this->modified    = $date->toSql();
        } else {
            $this->modified_by = 0;
        }

        if (!(int) $this->created) {
            $this->created = $date->toSql();
        }

        if (empty($this->created_by)) {
            $this->created_by = $user->id;
        }

        if (!(int) $this->modified) {
            $this->modified = $this->created;
        }

        if (empty($this->modified_by)) {
            $this->modified_by = $this->created_by;
        }

        if ((int) $this->default === 1) {
            // Verify that the default is unique for this workflow
            if (
                $table->load(
                    [
                    'default' => '1',
                    'extension' => $this->extension
                    ]
                )
            ) {
                $table->default = 0;
                $table->store();
            }
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to bind an associative array or object to the Table instance.
     * This method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   array|object  $src     An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public function bind($src, $ignore = array())
    {
        // Bind the rules.
        if (isset($src['rules']) && \is_array($src['rules'])) {
            $rules = new Rules($src['rules']);
            $this->setRules($rules);
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since  4.0.0
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        $parts = explode('.', $this->extension);

        $extension = array_shift($parts);

        return $extension . '.workflow.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since  4.0.0
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    /**
     * Get the parent asset id for the record
     *
     * @param   Table    $table  A Table object for the asset parent.
     * @param   integer  $id     The id for the asset
     *
     * @return  integer  The id of the asset's parent
     *
     * @since  4.0.0
     */
    protected function _getAssetParentId(Table $table = null, $id = null)
    {
        $assetId = null;

        $parts = explode('.', $this->extension);

        $extension = array_shift($parts);

        // Build the query to get the asset id for the parent category.
        $query = $this->getDbo()->getQuery(true)
            ->select($this->getDbo()->quoteName('id'))
            ->from($this->getDbo()->quoteName('#__assets'))
            ->where($this->getDbo()->quoteName('name') . ' = :extension')
            ->bind(':extension', $extension);

        // Get the asset id from the database.
        $this->getDbo()->setQuery($query);

        if ($result = $this->getDbo()->loadResult()) {
            $assetId = (int) $result;
        }

        // Return the asset id.
        if ($assetId) {
            return $assetId;
        } else {
            return parent::_getAssetParentId($table, $id);
        }
    }
}
