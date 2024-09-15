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
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Stage table
 *
 * @since  4.0.0
 */
class StageTable extends Table
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
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since  4.0.0
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__workflow_stages', 'id', $db, $dispatcher);
    }

    /**
     * Deletes workflow with transition and stages.
     *
     * @param   int  $pk  Extension ids to delete.
     *
     * @return  boolean  True on success.
     *
     * @since  4.0.0
     *
     * @throws  \UnexpectedValueException
     */
    public function delete($pk = null)
    {
        $db  = $this->getDbo();
        $app = Factory::getApplication();
        $pk  = (int) $pk;

        $query = $db->getQuery(true)
            ->select($db->quoteName('default'))
            ->from($db->quoteName('#__workflow_stages'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $pk, ParameterType::INTEGER);

        $isDefault = $db->setQuery($query)->loadResult();

        if ($isDefault) {
            $app->enqueueMessage(Text::_('COM_WORKFLOW_MSG_DELETE_IS_DEFAULT'), 'error');

            return false;
        }

        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__workflow_transitions'))
                ->where(
                    [
                        $db->quoteName('to_stage_id') . ' = :idTo',
                        $db->quoteName('from_stage_id') . ' = :idFrom',
                    ],
                    'OR'
                )
                ->bind([':idTo', ':idFrom'], $pk, ParameterType::INTEGER);

            $db->setQuery($query)->execute();

            return parent::delete($pk);
        } catch (\RuntimeException $e) {
            $app->enqueueMessage(Text::sprintf('COM_WORKFLOW_MSG_WORKFLOWS_DELETE_ERROR', $e->getMessage()), 'error');
        }

        return false;
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
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_STATE'));

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
                ->from($db->quoteName('#__workflow_stages'))
                ->where(
                    [
                        $db->quoteName('workflow_id') . ' = :id',
                        $db->quoteName('default') . ' = 1',
                    ]
                )
                ->bind(':id', $this->workflow_id, ParameterType::INTEGER);

            $id = $db->setQuery($query)->loadResult();

            // If there is no default stage => set the current to default to recover
            if (empty($id)) {
                $this->default = '1';
            } elseif ($id === $this->id) {
                // This stage is the default, but someone has tried to disable it => not allowed
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
        $table = new StageTable($this->getDbo(), $this->getDispatcher());

        if ($this->default == '1') {
            // Verify that the default is unique for this workflow
            if ($table->load(['default' => '1', 'workflow_id' => (int) $this->workflow_id])) {
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
    public function bind($src, $ignore = [])
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
        $k        = $this->_tbl_key;
        $workflow = new WorkflowTable($this->getDbo(), $this->getDispatcher());
        $workflow->load($this->workflow_id);

        $parts = explode('.', $workflow->extension);

        $extension = array_shift($parts);

        return $extension . '.stage.' . (int) $this->$k;
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
     * @param   ?Table    $table  A Table object for the asset parent.
     * @param   ?integer  $id     The id for the asset
     *
     * @return  integer  The id of the asset's parent
     *
     * @since  4.0.0
     */
    protected function _getAssetParentId(?Table $table = null, $id = null)
    {
        $asset = new Asset($this->getDbo(), $this->getDispatcher());

        $workflow = new WorkflowTable($this->getDbo(), $this->getDispatcher());
        $workflow->load($this->workflow_id);

        $parts = explode('.', $workflow->extension);

        $extension = array_shift($parts);

        $name = $extension . '.workflow.' . (int) $workflow->id;

        $asset->loadByName($name);
        $assetId = $asset->id;

        return !empty($assetId) ? $assetId : parent::_getAssetParentId($table, $id);
    }
}
