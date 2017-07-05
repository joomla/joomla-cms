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
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since   4.0
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		$this->typeAlias = 'com_workflow.title';
		parent::__construct('#__workflows', 'id', $db);
	}

	/**
	 * Deletes workflow with transition and statuses.
	 *
	 * @param   int  $pk  Extension ids to delete.
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

		// Delete the update site from all tables.
		try
		{
			$query = $db->getQuery(true)
				->delete($db->qn('#__workflow_status'))
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
			// Gets the update site names.
			$query = $db->getQuery(true)
				->select($db->qn(array('id', 'title')))
				->from($db->qn('#__workflows'))
				->where($db->qn('id') . ' = ' . (int) $pk);
			$db->setQuery($query);
			$workflows = $db->loadObjectList();

			$app->enqueueMessage(\JText::sprintf('COM_WORKFLOW_MSG_WORKFLOWS_DELETE_ERROR', $workflows[$pk]->title, $e->getMessage()), 'error');

			return;
		}

		return false;
	}

}
