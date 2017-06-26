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
			\JText::_('COM_WORKFLOW_SUBMENU_STATUS'),
			'index.php?option=com_workflow&view=statuses&workflow_id=' . $name[1],
			$name[0] == 'statuses`'
		);

		\JHtmlSidebar::addEntry(
			\JText::_('COM_WORKFLOW_SUBMENU_TRANSITIONS'),
			'index.php?option=com_workflow&view=transitions&workflow_id=' . $name[1],
			$name[0] == 'transitions'
		);
	}

	/**
	 * Get SQL for select statuses field
	 *
	 * @param   string  $fieldName   The name of field to which will be that sql
	 * @param   int     $workflowID  ID of workflow
     *
	 * @return  string
	 *
	 * @since   4.0
	 */
	public static function getStatusesSQL($fieldName, $workflowID)
	{
		return "SELECT `id` AS `value`, `title` AS `$fieldName` FROM #__workflow_status WHERE workflow_id=$workflowID";
	}
}
