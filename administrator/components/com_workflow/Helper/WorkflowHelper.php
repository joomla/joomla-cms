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
	 * @param   string  $extension  The name of extension given.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public static function addSubmenu($vName, $extension)
	{
		\JHtmlSidebar::addEntry(
			\JText::_('COM_WORKFLOW_SUBMENU_STATUS'),
			'index.php?option=com_workflow&view=status&extension=' . $extension,
			$vName == 'status'
		);

		\JHtmlSidebar::addEntry(
			\JText::_('COM_WORKFLOW_SUBMENU_TRANSITIONS'),
			'index.php?option=com_workflow&view=transitions&extension=' . $extension,
			$vName == 'transitions'
		);
	}
}
