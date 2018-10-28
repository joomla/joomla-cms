<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Path;
use Joomla\String\StringHelper;

/**
 * Actionlogs component helper.
 *
 * @since  3.9.0
 */
class ActionlogsHelper
{
	/**
	 * Method to convert logs objects array to an iterable type for use with a CSV export
	 *
	 * @param   array|Traversable  $data  The logs data objects to be exported
	 *
	 * @return  array|Generator  For PHP 5.5 and newer, a Generator is returned; PHP 5.4 and earlier use an array
	 *
	 * @since   3.9.0
	 * @throws  InvalidArgumentException
	 */
	public static function getCsvData($data)
	{
		if (!is_iterable($data))
		{
			throw new InvalidArgumentException(
				sprintf(
					'%s() requires an array or object implementing the Traversable interface, a %s was given.',
					__METHOD__,
					gettype($data) === 'object' ? get_class($data) : gettype($data)
				)
			);
		}

		if (version_compare(PHP_VERSION, '5.5', '>='))
		{
			// Only include the PHP 5.5 helper in this conditional to prevent the potential of parse errors for PHP 5.4 or earlier
			JLoader::register('ActionlogsHelperPhp55', __DIR__ . '/actionlogsphp55.php');

			return ActionlogsHelperPhp55::getCsvAsGenerator($data);
		}

		$rows = array();

		// Header row
		$rows[] = array('Id', 'Message', 'Date', 'Extension', 'User', 'Ip');

		foreach ($data as $log)
		{
			$date      = new JDate($log->log_date, new DateTimeZone('UTC'));
			$extension = strtok($log->extension, '.');

			static::loadTranslationFiles($extension);

			$rows[] = array(
				'id'         => $log->id,
				'message'    => strip_tags(static::getHumanReadableLogMessage($log, false)),
				'date'       => $date->format('Y-m-d H:i:s T'),
				'extension'  => JText::_($extension),
				'name'       => $log->name,
				'ip_address' => JText::_($log->ip_address),
			);
		}

		return $rows;
	}

	/**
	 * Load the translation files for an extension
	 *
	 * @param   string  $extension  Extension name
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public static function loadTranslationFiles($extension)
	{
		static $cache = array();
		$extension = strtolower($extension);

		if (isset($cache[$extension]))
		{
			return;
		}

		$lang   = JFactory::getLanguage();

		switch (substr($extension, 0, 3))
		{
			case 'com':
			default:
				$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
				break;

			case 'lib':
				$source = JPATH_LIBRARIES . '/' . substr($extension, 4);
				break;

			case 'mod':
				$source = JPATH_SITE . '/modules/' . $extension;
				break;

			case 'plg':
				$parts = explode('_', $extension, 3);
				$source = JPATH_PLUGINS . '/' . $parts[1] . '/' . $parts[2];
				break;

			case 'tpl':
				$source = JPATH_BASE . '/templates/' . substr($extension, 4);
				break;

		}

		$lang->load($extension, JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load($extension, $source, null, false, true);

		if (!$lang->hasKey(strtoupper($extension)))
		{
			$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, true)
				|| $lang->load($extension . '.sys', $source, null, false, true);
		}

		$cache[$extension] = true;
	}

	/**
	 * Get parameters to be
	 *
	 * @param   string  $context  The context of the content
	 *
	 * @return  mixed  An object contains content type parameters, or null if not found
	 *
	 * @since   3.9.0
	 */
	public static function getLogContentTypeParams($context)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.*')
			->from($db->quoteName('#__action_log_config', 'a'))
			->where($db->quoteName('a.type_alias') . ' = ' . $db->quote($context));

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get human readable log message for a User Action Log
	 *
	 * @param   stdClass  $log            A User Action log message record
	 * @param   boolean   $generateLinks  Flag to disable link generation when creating a message
	 *
	 * @return  string
	 *
	 * @since   3.9.0
	 */
	public static function getHumanReadableLogMessage($log, $generateLinks = true)
	{
		static $links = array();

		$message     = JText::_($log->message_language_key);
		$messageData = json_decode($log->message, true);

		// Special handling for translation extension name
		if (isset($messageData['extension_name']))
		{
			static::loadTranslationFiles($messageData['extension_name']);
			$messageData['extension_name'] = JText::_($messageData['extension_name']);
		}

		$linkMode = JFactory::getApplication()->get('force_ssl', 0) >= 1 ? 1 : -1;

		foreach ($messageData as $key => $value)
		{
			// Convert relative url to absolute url so that it is clickable in action logs notification email
			if ($generateLinks && StringHelper::strpos($value, 'index.php?') === 0)
			{
				if (!isset($links[$value]))
				{
					$links[$value] = JRoute::link('administrator', $value, false, $linkMode);
				}

				$value = $links[$value];
			}

			$message = str_replace('{' . $key . '}', JText::_($value), $message);
		}

		return $message;
	}

	/**
	 * Get link to an item of given content type
	 *
	 * @param   string   $component
	 * @param   string   $contentType
	 * @param   integer  $id
	 * @param   string   $urlVar
	 *
	 * @return  string  Link to the content item
	 *
	 * @since   3.9.0
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

	/**
	 * Load both enabled and disabled actionlog plugins language file.
	 *
	 * It is used to make sure actions log is displayed properly instead of only language items displayed when a plugin is disabled.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public static function loadActionLogPluginsLanguage()
	{
		$lang = JFactory::getLanguage();
		$db   = JFactory::getDbo();

		// Get all (both enabled and disabled) actionlog plugins
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'folder',
						'element',
						'params',
						'extension_id'
					),
					array(
						'type',
						'name',
						'params',
						'id'
					)
				)
			)
			->from('#__extensions')
			->where('type = ' . $db->quote('plugin'))
			->where('folder = ' . $db->quote('actionlog'))
			->where('state IN (0,1)')
			->order('ordering');
		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$rows = array();
		}

		if (empty($rows))
		{
			return;
		}

		foreach ($rows as $row)
		{
			$name      = $row->name;
			$type      = $row->type;
			$extension = 'Plg_' . $type . '_' . $name;
			$extension = strtolower($extension);

			// If language already loaded, don't load it again.
			if ($lang->getPaths($extension))
			{
				continue;
			}

			$lang->load($extension, JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load($extension, JPATH_PLUGINS . '/' . $type . '/' . $name, null, false, true);
		}

		// Load com_privacy too.
		$lang->load('com_privacy', JPATH_ADMINISTRATOR, null, false, true);
	}
}
