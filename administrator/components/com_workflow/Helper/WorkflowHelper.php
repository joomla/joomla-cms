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
	 * Configure the Submenu links.
	 *
	 * @param   string  $extension  The extension from where Helper can find.
	 * @param   string  $method     Method from that extension to invoke.
	 * @param   string  $parameter  Parameters for that method.
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

		$parts     = explode('.', $extension);
		$component = $parts[0];

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		// Try to find the component helper.
		$eName = str_replace('com_', '', $component);
		$file  = \JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			$prefix = ucfirst(str_replace('com_', '', $component));
			$cName  = $prefix . 'Helper';

			\JLoader::register($cName, $file);

			if (class_exists($cName) && is_callable(array($cName, $method)))
			{
				$lang = \JFactory::getLanguage();

				// Loading language file from the administrator/language directory then
				// loading language file from the administrator/components/*extension*/language directory
				$lang->load($component, JPATH_BASE, null, false, true)
				|| $lang->load($component, \JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, true);

				return call_user_func(array($cName, $method), $parameter);
			}
		}

		return null;
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
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName(array('id', 'title'), array('value', $fieldName)))
			->from($db->quoteName('#__workflow_states'))
			->where($db->quoteName('workflow_id') . ' = ' . (int) $workflowID)
			->andWhere($db->quoteName('published') . ' =1');

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
			case 0:
				return "COM_WORKFLOW_UNPUBLISHED";
			case 1:
				return "COM_WORKFLOW_PUBLISHED";
			case -2:
				return "COM_WORKFLOW_TRASHED";
		}
	}

	/**
	 * Runs transitions for each item passing in attributes.
	 *
	 * @param   array   $pks             ids of articles
	 * @param   array   $transitions     ids of transitions
	 * @param   string  $extension       name of extension
	 * @param   string  $componentTable  name of table from where are ids
	 *
	 * @return  resource|boolean
	 *
	 * @since   4.0
	 */
	public static function runTransitions($pks, $transitions, $extension, $componentTable)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$select = $db->quoteName(
			array(
				'tran.id',
				'tran.to_state_id',
				'tran.from_state_id'
			)
		);

		$query
			->select($select)
			->from($db->quoteName('#__workflow_transitions', 'tran'))
			->where($db->qn('tran.id') . ' IN (' . implode(',', $transitions) . ')')
			->andWhere($db->qn('tran.published') . '=1');

		$result = $db->setQuery($query)->loadObjectList();

		$query
			->select($db->quoteName(array("a.state", "a.id")))
			->from($db->qn($componentTable, 'a'))
			->where($db->qn("a.id") . ' IN (' . implode(",", $pks) . ')');

		$items = $db->setQuery($query)->loadAssocList('id', 'state');

		foreach ($result as $k => $v)
		{
			$query->clear();
			$pk = (int) $pks[0];

			if ($pk > 0)
			{
				try
				{
					$updated = self::callMethodFromHelper($extension, 'updateAfterTransaction', $v->to_state_id);

					if (!$updated)
					{
						if ($items[$pk] === $v->from_state_id)
						{
							$query->clear();
							$query
								->update($componentTable)
								->set(
									array(
										$db->qn('state') . '=' . $db->quote($v->to_state_id)
									)
								)
								->where($db->qn('id') . '=' . $pk);
							$db->setQuery($query);

							return $db->execute();
						}

						return false;
					}

					return $updated;
				}
				catch (\Exception $e)
				{
					return false;
				}
			}
		}
	}
}
