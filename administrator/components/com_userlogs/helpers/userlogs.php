<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Userlogs component helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class UserlogsHelper
{
	/**
	 * Method to extract data array of objects into CSV file
	 *
	 * @param   array  $data  The logs data to be exported
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function dataToCsv($data)
	{
		$date     = JFactory::getDate();
		$filename = "logs_" . $date;

		$app = JFactory::getApplication();
		$app->setHeader('Content-Type', 'application/csv', true)
			->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.csv"', true)
			->setHeader('Cache-Control', 'must-revalidate', true);

		$app->sendHeaders();

		$headers = array('Id', 'Message', 'Date', 'Extension', 'User', 'Ip');

		$fp = fopen('php://temp', 'r+');
		ob_end_clean();

		fputcsv($fp, $headers);

		foreach ($data as $log)
		{
			$log               = (array) $log;
			$log['ip_address'] = JText::_($log['ip_address']);
			$log['extension']  = self::translateExtensionName(strtoupper(strtok($log['extension'], '.')));

			$app->triggerEvent('onLogMessagePrepare', array(&$log['message'], $log['extension']));

			fputcsv($fp, $log, ',');
		}

		rewind($fp);
		$content = stream_get_contents($fp);
		echo $content;
		fclose($fp);

		$app->close();
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
				->from($db->quoteName('#__user_logs_tables_data', 'a'))
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
		$message = JText::_($log->message_language_key);
		$messageData = json_decode($log->message, true);

		// Special handling for translation extension name
		if (isset($messageData['extension_name']))
		{
			$messageData['extension_name'] = self::translateExtensionName($messageData['extension_name']);
		}

		foreach ($messageData as $key => $value)
		{
			$message = str_replace('{'.$key.'}', $value, $message);
		}

		return $message;
	}
}
