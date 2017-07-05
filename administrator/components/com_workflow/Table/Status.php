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
class Status extends Table
{

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		parent::__construct('#__workflow_status', 'id', $db);
	}

	/**
	 * Deletes workflow with transition and statuses.
	 *
	 * @param   int  $pk  Extension ids to delete.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0
	 *
	 * @throws  \UnexpectedValueException
	 */
	public function delete($pk = null)
	{
		// @TODO: correct ACL check should be done in $model->canDelete(...) not here
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
				->delete($db->qn('#__workflow_transitions'))
				->where($db->qn('to_status_id') . ' = ' . (int) $pk, 'OR')
				->where($db->qn('from_status_id') . ' = ' . (int) $pk);

			$db->setQuery($query)->execute();

			return parent::delete($pk);

		}
		catch (\RuntimeException $e)
		{
			// Gets the update site names.
			$query = $db->getQuery(true)
				->select($db->qn(array('id', 'title')))
				->from($db->qn('#__workflows'))
				->where($db->qn('id') . ' = ' . (int) $pk);

			$workflows = $db->setQuery($query)->loadObjectList();

			$app->enqueueMessage(\JText::sprintf('COM_WORKFLOW_MSG_WORKFLOWS_DELETE_ERROR', $workflows[$pk]->title, $e->getMessage()), 'error');
		}

		return false;
	}
}
