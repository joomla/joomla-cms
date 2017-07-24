<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * Category table
 *
 * @since  1.6
 */
class Workflow extends Table
{

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver $db Database connector object
	 *
	 * @since   4.0
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		$this->typeAlias = '{extension}.workflow';
		parent::__construct('#__workflows', 'id', $db);
		$this->access = (int) Factory::getConfig()->get('access');
	}

	/**
	 * Deletes workflow with transition and states.
	 *
	 * @param   int $pk Extension ids to delete.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 *
	 * @throws  \Exception on ACL error
	 */
	public function delete($pk = null)
	{
		if (!\JFactory::getUser()->authorise('core.delete', 'com_installer'))
		{
			throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
		}

		$db  = $this->getDbo();
		$app = \JFactory::getApplication();

		// Gets the update site names.
		$query = $db->getQuery(true)
			->select($db->qn(array('id', 'title')))
			->from($db->qn('#__workflows'))
			->where($db->qn('id') . ' = ' . (int) $pk);
		$db->setQuery($query);
		$workflow = $db->loadResult();

		if ($workflow->default)
		{
			$app->enqueueMessage(\JText::sprintf('COM_WORKFLOW_MSG_DELETE_DEFAULT', $workflow->title), 'error');

			return false;
		}

		// Delete the update site from all tables.
		try
		{
			$query = $db->getQuery(true)
				->delete($db->qn('#__workflow_states'))
				->where($db->qn('workflow_id') . ' = ' . (int) $pk);
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true)
				->delete($db->qn('#__workflow_transitions'))
				->where($db->qn('workflow_id') . ' = ' . (int) $pk);
			$db->setQuery($query);
			$db->execute();

			return parent::delete($pk);

		}
		catch (\RuntimeException $e)
		{
			$app->enqueueMessage(\JText::sprintf('COM_WORKFLOW_MSG_WORKFLOWS_DELETE_ERROR', $workflow->title, $e->getMessage()), 'error');

			return;
		}

		return false;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return $this->extension . '.workflow.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   1.6
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
	 * @since   1.6
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$assetId = null;

		// Build the query to get the asset id for the parent category.
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('id'))
			->from($this->_db->quoteName('#__assets'))
			->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote($this->extension));

		// Get the asset id from the database.
		$this->_db->setQuery($query);

		if ($result = $this->_db->loadResult())
		{
			$assetId = (int) $result;
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}
}
