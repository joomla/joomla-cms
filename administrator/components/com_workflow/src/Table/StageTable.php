<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

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
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since  4.0.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__workflow_stages', 'id', $db);

		$this->access = (int) Factory::getApplication()->get('access');
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
		// @TODO: correct ACL check should be done in $model->canDelete(...) not here
		if (!Factory::getUser()->authorise('core.delete', 'com_workflows'))
		{
			throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
		}

		$db  = $this->getDbo();
		$app = Factory::getApplication();

		// Gets the update site names.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title')))
			->from($db->quoteName('#__workflow_stages'))
			->where($db->quoteName('id') . ' = ' . (int) $pk);
		$db->setQuery($query);
		$stage = $db->loadResult();

		if ($stage->default)
		{
			$app->enqueueMessage(Text::sprintf('COM_WORKFLOW_MSG_DELETE_DEFAULT', $stage->title), 'error');

			return false;
		}

		// Delete the update site from all tables.
		try
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__workflow_transitions'))
				->where($db->quoteName('to_stage_id') . ' = ' . (int) $pk, 'OR')
				->where($db->quoteName('from_stage_id') . ' = ' . (int) $pk);

			$db->setQuery($query)->execute();

			return parent::delete($pk);
		}
		catch (\RuntimeException $e)
		{
			$app->enqueueMessage(Text::sprintf('COM_WORKFLOW_MSG_WORKFLOWS_DELETE_ERROR', $stage->title, $e->getMessage()), 'error');
		}

		return false;
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success
	 *
	 * @see     Table::check()
	 * @since   4.0
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if (trim($this->title) === '')
		{
			$this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_STATE'));

			return false;
		}

		if (!empty($this->default))
		{
			if ((int) $this->published !== 1)
			{
				$this->setError(Text::_('COM_WORKFLOW_ITEM_MUST_PUBLISHED'));

				return false;
			}
		}
		else
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			$query
				->select($db->quoteName('id'))
				->from($db->quoteName('#__workflow_stages'))
				->where($db->quoteName('workflow_id') . '=' . $this->workflow_id)
				->where($db->quoteName('default') . '= 1');

			$stage = $db->setQuery($query)->loadObject();

			if (empty($stage) || $stage->id === $this->id)
			{
				$this->default = '1';

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
	 * @since   4.0
	 */
	public function store($updateNulls = true)
	{
		$table = new StageTable($this->getDbo());

		if ($this->default == '1')
		{
			// Verify that the default is unique for this workflow
			if ($table->load(array('default' => '1', 'workflow_id' => (int) $this->workflow_id)))
			{
				$table->default = 0;
				$table->store();
			}
		}

		return parent::store($updateNulls);
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
		$workflow = new WorkflowTable($this->getDbo());
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
	 * @param   Table    $table  A JTable object for the asset parent.
	 * @param   integer  $id     The id for the asset
	 *
	 * @return  integer  The id of the asset's parent
	 *
	 * @since  4.0.0
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$asset = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));

		$workflow = new WorkflowTable($this->getDbo());
		$workflow->load($this->workflow_id);

		$parts = explode('.', $workflow->extension);

		$extension = array_shift($parts);

		$name = $extension . '.workflow.' . (int) $workflow->id;

		$asset->loadByName($name);
		$assetId = $asset->id;

		return !empty($assetId) ? $assetId : parent::_getAssetParentId($table, $id);
	}
}
