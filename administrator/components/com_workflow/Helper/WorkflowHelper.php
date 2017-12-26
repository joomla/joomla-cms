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
 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
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

		return false;
	}

	/**
	 * Get SQL for select states field
	 *
	 * @param   string  $fieldName   The name of field to which will be that sql
	 * @param   int     $workflowID  ID of workflo
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getStatesSQL($fieldName, $workflowID)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName(['id', 'title'], array('value', $fieldName)))
			->from($db->quoteName('#__workflow_states'))
			->where($db->quoteName('workflow_id') . ' = ' . (int) $workflowID)
			->where($db->quoteName('published') . ' = 1');

		return (string) $query;
	}

	/**
	 * Get name by passing number
	 *
	 * @param   int  $number  Enum of condition
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * Runs a single transition for single item
	 *
	 * @param   array   $pk              id of article
	 * @param   array   $transitionId    id of transition
	 * @param   string  $extension       name of extension
	 *
	 * @return bool|resource
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function runTransition($pk, $transitionId, $extension)
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
			->where($db->qn('tran.id') . ' = ' . $db->quote($transitionId))
			->andWhere($db->qn('tran.published') . ' = 1');

		$transitionResult = $db->setQuery($query)->loadObject();

		$associateEntry = self::getAssociatedEntry($pk);

		if ($associateEntry->state_id != $transitionResult->from_state_id)
		{
			return false;
		}

		// If it can handle by itself, let's do it
		if (self::callMethodFromHelper($extension, 'updateAfterTransaction', $transitionResult->to_state_id))
		{
			return true;
		}

		// Use default handling
		return self::updateAssociationByItemId($pk, $transitionResult->to_state_id, $extension);
	}

	/**
	 * Adds an association for the workflow_associations table
	 *
	 * @param   int     $itemId       id of content
	 * @param   int     $stateId      id of state
	 * @param   string  $extension    extension type
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function addAssociation($itemId, $stateId, $extension = 'com_content')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->insert($db->qn('#__workflow_associations'))
			->columns($db->quoteName(array('item_id', 'state_id', 'extension')))
			->values((int) $itemId . ', ' . (int) $stateId . ', ' . $db->quote($extension));

		$db->setQuery($query)->execute();
	}

	/**
	 * Gets an association form the workflow_associations table
	 *
	 * @param   int     $itemId       id of content
	 * @param   string  $extension    extension type
	 *
	 * @return object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getAssociatedEntry($itemId, $extension = 'com_content')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->qn('#__workflow_associations'))
			->where($db->qn('item_id') . '=' . (int) $itemId)
			->where($db->qn('extension') . '=' . $db->quote($extension));

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * Removes an association form the workflow_associations table
	 *
	 * @param   int     $itemId       id of content
	 * @param   string  $extension    extension type
	 *
	 * @return boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function removeAssociationsByItemIds($pks, $extension = 'com_content')
	{
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query
				->delete($db->qn('#__workflow_associations'))
				->where($db->qn('item_id') . 'IN (' . implode(',', $pks) . ')')
				->andWhere($db->qn('extension') . '=' . $db->quote($extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Updates an association in the workflow_associations table
	 *
	 * @param   int     $itemId       id of content
	 * @param   int     $stateId       id of state
	 * @param   string  $extension    extension type
	 *
	 * @return boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function updateAssociationByItemId($itemId, $stateId, $extension = 'com_content')
	{
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query
				->update($db->qn('#__workflow_associations'))
				->set($db->qn('state_id') . '=' . (int) $stateId)
				->where($db->qn('item_id') . '=' . (int) $itemId)
				->where($db->qn('extension') . '=' . $db->quote($extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Updates multiple associations in the workflow_associations table to a given state
	 *
	 * @param   array   $itemIds      ids of content
	 * @param   int     $stateId      id of state
	 * @param   string  $extension    extension type
	 *
	 * @return boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function updateAssociationOfItemIdList($itemIds, $stateId, $extension = 'com_content')
	{
		if (empty($itemIds))
		{
			return false;
		}

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query
				->update($db->qn('#__workflow_associations'))
				->set($db->qn('state_id') . '=' . (int) $stateId)
				->where($db->qn('item_id') . ' IN (' . implode(', ', $itemIds) . ')')
				->andWhere($db->qn('extension') . '=' . $db->quote($extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Gets the to_state of a transition
	 *
	 * @param   int  $transitionId    id of transition
	 *
	 * @return object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getUpdatedState($transitionId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__workflow_transitions', 'wt'))
			->innerJoin($db->quoteName('#__workflow_states', 'ws') . ' ON ws.id = wt.to_state_id')
			->where($db->qn('wt.id') . '=' . $db->quote($transitionId));

		return $db->setQuery($query)->loadObject();
	}
}
