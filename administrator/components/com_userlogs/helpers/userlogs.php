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
	 * @param   array  $data  Has the data to be exported
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function dataToCsv($data)
	{
		$date = JFactory::getDate();
		$filename = "Logs_" . $date;
		$data = json_decode(json_encode($data), true);
		$dispatcher = JEventDispatcher::getInstance();

		$app = JFactory::getApplication();
		$app->setHeader('Content-Type', 'application/csv', true)
			->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.csv"', true)
			->setHeader('Cache-Control', 'must-revalidate', true);

		$app->sendHeaders();

		$fp = fopen('php://temp', 'r+');
		ob_end_clean();

		foreach ($data as $log)
		{
			$dispatcher->trigger('onLogMessagePrepare', array (&$log['message'], $log['extension']));
			$log['ip_address'] = JText::_($log['ip_address']);
			$log['extension'] = self::translateExtensionName(strtoupper(strtok($log['extension'], '.')));

			fputcsv($fp, $log, ',');
		}

		rewind($fp);
		$content = stream_get_contents($fp);
		echo $content;
		fclose($fp);

		$app->close();
	}

	/**
	 * Change the retrived extension name to more user friendly name
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
	 * @param   string   $context  The context of the content
	 *
	 * @return  mixed  An array of parameters, or false on error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getLogMessageParams($context)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
				->select('a.*')
				->from($db->quoteName('#__user_logs_tables_data', 'a'))
				->where($db->quoteName('a.type_alias') . ' = "' . $context . '"');

		$db->setQuery($query);

		$items = $db->loadObjectList();

		if (empty($items))
		{
			return false;
		}

		return $items[0];
	}

	/**
	 * Method to retrive data by primary keys from a table
	 *
	 * @param   array   $pks          An array of primary key ids of the content that has changed state.
	 * @param   string  $field        The field to get from the table
	 * @param   string  $tableType    The type (name) of the JTable class to get an instance of.
	 * @param   string  $tablePrefix  An optional prefix for the table class name.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getDataByPks($pks, $field, $tableType, $tablePrefix = 'JTable')
	{
		$items = array();
		$table = JTable::getInstance($tableType, $tablePrefix);

		foreach ($pks as $pk)
		{
			$table->load($pk);
			$items[] = $table->get($field);
		}

		return $items;
	}
}
