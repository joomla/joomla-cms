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
	 * Configure the Submenu links.
	 *
	 * @param   string  $extension      The extension from where Helper can find.
	 * @param   string  $method         Method from that extension to invoke.
	 * @param   array   $parameter      Parameters for that method.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public static function callMethodFromHelper($extension, $method, $parameter)
	{
		// Avoid nonsense situation.
		if ($extension == 'com_workflows')
		{
			return false;
		}

		$parts = explode('.', $extension);
		$component = $parts[0];

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		// Try to find the component helper.
		$eName = str_replace('com_', '', $component);
		$file = \JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			$prefix = ucfirst(str_replace('com_', '', $component));
			$cName = $prefix . 'Helper';

			\JLoader::register($cName, $file);

			if (class_exists($cName))
			{
				if (is_callable(array($cName, $method)))
				{
					$lang = \JFactory::getLanguage();

					// Loading language file from the administrator/language directory then
					// loading language file from the administrator/components/*extension*/language directory
					$lang->load($component, JPATH_BASE, null, false, true)
					|| $lang->load($component, \JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, true);

					return call_user_func(array($cName, $method), $parameter);
				}
			}
		}

		return true;
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

		$query
			->select($db->quoteName(['id', 'title'], ['value', $fieldName]))
			->from($db->quoteName('#__workflow_states'))
			->where($db->quoteName('workflow_id') . ' = ' . (int) $workflowID)
			->andWhere($db->quoteName('published') . ' IN (0, 1)');

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
