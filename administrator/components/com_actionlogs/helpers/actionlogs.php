<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Path;

/**
 * Actionlogs component helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class ActionlogsHelper
{
	/**
	 * Method to convert logs objects array to associative array use for CSV export
	 *
	 * @param   array  $data  The logs data objects to be exported
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getCsvData($data)
	{
		$rows = array();

		// Header row
		$rows[] = array('Id', 'Message', 'Date', 'Extension', 'User', 'Ip');

		foreach ($data as $log)
		{
			$row               = array();
			$row['id']         = $log->id;
			$row['message']    = strip_tags(self::getHumanReadableLogMessage($log));
			$row['date']       = $log->log_date;
			$row['extension']  = self::translateExtensionName(strtoupper(strtok($log->extension, '.')));
			$row['name']       = $log->name;
			$row['ip_address'] = JText::_($log->ip_address);

			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Change the retrieved extension name to more user friendly name
	 *
	 * @param   string  $extension  Extension name
	 *
	 * @return  string  Translated extension name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function translateExtensionName($extension)
	{
		$lang   = JFactory::getLanguage();
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;

		$lang->load(strtolower($extension), JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load(strtolower($extension), $source, null, false, true);

		return JText::_($extension);
	}

	/**
	 * Get parameters to be
	 *
	 * @param   string  $context  The context of the content
	 *
	 * @return  mixed  An object contains content type parameters, or null if not found
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getLogContentTypeParams($context)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
				->select('a.*')
				->from($db->quoteName('#__action_logs_tables_data', 'a'))
				->where($db->quoteName('a.type_alias') . ' = ' .$db->quote($context));

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to retrieve data by primary keys from a table
	 *
	 * @param   array   $pks      An array of primary key ids of the content that has changed state.
	 * @param   string  $field    The field to get from the table
	 * @param   string  $idField  The primary key of the table
	 * @param   string  $table    The database table to get data from
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getDataByPks($pks, $field, $idField, $table)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array($idField, $field)))
			->from($db->quoteName($table))
			->where($db->quoteName($idField) . ' IN (' . implode(',', $pks) . ')');
		$db->setQuery($query);

		try
		{
			return $db->loadObjectList($idField);
		}
		catch (RuntimeException $e)
		{
			return array();
		}
	}

	/**
	 * Get human readable log message for a User Action Log
	 *
	 * @param   stdClass  $log  A User Action log message record
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getHumanReadableLogMessage($log)
	{
		$message     = JText::_($log->message_language_key);
		$messageData = json_decode($log->message, true);

		// Special handling for translation extension name
		if (isset($messageData['extension_name']))
		{
			$messageData['extension_name'] = self::translateExtensionName($messageData['extension_name']);
		}

		foreach ($messageData as $key => $value)
		{
			$message = str_replace('{' . $key . '}', JText::_($value), $message);
		}

		return $message;
	}

	/**
	 * Get link to an item of given content type
	 *
	 * @param   string  $component
	 * @param   string  $contentType
	 * @param   int     $id
	 * @param   string  $urlVar
	 *
	 * @return  string  Link to the content item
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getContentTypeLink($component, $contentType, $id, $urlVar = 'id')
	{
		// Try to find the component helper.
		$eName = str_replace('com_', '', $component);
		$file  = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			$prefix = ucfirst(str_replace('com_', '', $component));
			$cName  = $prefix . 'Helper';

			JLoader::register($cName, $file);

			if (class_exists($cName) && is_callable(array($cName, 'getContentTypeLink')))
			{
				return $cName::getContentTypeLink($contentType, $id);
			}
		}

		if (empty($urlVar))
		{
			$urlVar = 'id';
		}

		// Return default link to avoid having to implement getContentTypeLink in most of our components
		return 'index.php?option=' . $component . '&task=' . $contentType . '.edit&' . $urlVar . '=' . $id;
	}
}
