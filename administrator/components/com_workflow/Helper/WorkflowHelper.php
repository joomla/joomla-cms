<?php
/**
 * Created by PhpStorm.
 * User: janek
 * Date: 20.06.17
 * Time: 19:59
 */

namespace Joomla\Component\Workflow\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;

class WorkflowHelper extends ContentHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName      The name of the active view.
	 * @param   string  $workflowID  The name of extension given.
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
}
