<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  4.0
 */
class WorkflowHelper extends ContentHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public static function addSubmenu($vName)
	{
		$name = explode(".", $vName);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_WORKFLOW_STATE'),
			'index.php?option=com_workflow&view=states&workflow_id=' . $name[1] . "&extension=" . $name[2],
			$name[0] == 'states`'
		);

		\JHtmlSidebar::addEntry(
			\JText::_('COM_WORKFLOW_TRANSITION'),
			'index.php?option=com_workflow&view=transitions&workflow_id=' . $name[1] . "&extension=" . $name[2],
			$name[0] == 'transitions'
		);
	}

	/**
	 * Get SQL for select states field
	 *
	 * @param   string  $fieldName   The name of field to which will be that sql
	 * @param   int     $workflowID  ID of workflo
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	public static function getStatesSQL($fieldName, $workflowID)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$select[] = $db->qn('id') . ' AS ' . $db->qn('value');
		$select[] = $db->qn('title') . ' AS ' . $db->qn($db->escape($fieldName));

		$query
			->select($select)
			->from($db->qn('#__workflow_states'))
			->where($db->qn('workflow_id') . ' = ' . $workflowID);

		return (string) $query;
	}

	/**
	 * Get name by passing number
	 *
	 * @param   int  $number  Enum of condition
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	public static function getConditionName($number)
	{
		switch ($number)
		{
			case 1:
				return "COM_WORKFLOW_TRASHED";
			case 2:
				return "COM_WORKFLOW_UNPUBLISHED";
			case 3:
				return "COM_WORKFLOW_PUBLISHED";
		}
	}
}
