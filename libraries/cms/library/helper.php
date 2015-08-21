<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Library
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Library helper class
 *
 * @since  3.2
 */
class JLibraryHelper
{
	/**
	 * The component list cache
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected static $libraries = array();

	/**
	 * Get the library information.
	 *
	 * @param   string   $element  Element of the library in the extensions table.
	 * @param   boolean  $strict   If set and the library does not exist, the enabled attribute will be set to false.
	 *
	 * @return  object   An object with the library's information.
	 *
	 * @since   3.2
	 */
	public static function getLibrary($element, $strict = false)
	{
		// Is already cached ?
		if (isset(static::$libraries[$element]))
		{
			return static::$libraries[$element];
		}

		if (static::_load($element))
		{
			$result = static::$libraries[$element];
		}
		else
		{
			$result = new stdClass;
			$result->enabled = $strict ? false : true;
			$result->params = new Registry;
		}

		return $result;
	}

	/**
	 * Checks if a library is enabled
	 *
	 * @param   string  $element  Element of the library in the extensions table.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public static function isEnabled($element)
	{
		$result = static::getLibrary($element, true);

		return $result->enabled;
	}

	/**
	 * Gets the parameter object for the library
	 *
	 * @param   string   $element  Element of the library in the extensions table.
	 * @param   boolean  $strict   If set and the library does not exist, false will be returned
	 *
	 * @return  Registry  A Registry object.
	 *
	 * @see     Registry
	 * @since   3.2
	 */
	public static function getParams($element, $strict = false)
	{
		$library = static::getLibrary($element, $strict);

		return $library->params;
	}

	/**
	 * Save the parameters object for the library
	 *
	 * @param   string    $element  Element of the library in the extensions table.
	 * @param   Registry  $params   Params to save
	 *
	 * @return  Registry  A Registry object.
	 *
	 * @see     Registry
	 * @since   3.2
	 */
	public static function saveParams($element, $params)
	{
		if (static::isEnabled($element))
		{
			// Save params in DB
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__extensions'))
				->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
				->where($db->quoteName('type') . ' = ' . $db->quote('library'))
				->where($db->quoteName('element') . ' = ' . $db->quote($element));
			$db->setQuery($query);

			$result = $db->execute();

			// Update params in libraries cache
			if ($result && isset(static::$libraries[$element]))
			{
				static::$libraries[$element]->params = $params;
			}

			return $result;
		}

		return false;
	}

	/**
	 * Load the installed libraryes into the libraries property.
	 *
	 * @param   string  $element  The element value for the extension
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	protected static function _load($element)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('extension_id AS id, element AS "option", params, enabled')
			->from('#__extensions')
			->where($db->quoteName('type') . ' = ' . $db->quote('library'))
			->where($db->quoteName('element') . ' = ' . $db->quote($element));
		$db->setQuery($query);

		$cache = JFactory::getCache('_system', 'callback');

		try
		{
			static::$libraries[$element] = $cache->get(array($db, 'loadObject'), null, $element, false);
		}
		catch (RuntimeException $e)
		{
			// Fatal error.
			JLog::add(JText::sprintf('JLIB_APPLICATION_ERROR_LIBRARY_NOT_LOADING', $element, $e->getMessage()), JLog::WARNING, 'jerror');

			return false;
		}

		if (empty(static::$libraries[$element]))
		{
			// Fatal error.
			$error = JText::_('JLIB_APPLICATION_ERROR_LIBRARY_NOT_FOUND');
			JLog::add(JText::sprintf('JLIB_APPLICATION_ERROR_LIBRARY_NOT_LOADING', $element, $error), JLog::WARNING, 'jerror');

			return false;
		}

		// Convert the params to an object.
		if (is_string(static::$libraries[$element]->params))
		{
			$temp = new Registry;
			$temp->loadString(static::$libraries[$element]->params);
			static::$libraries[$element]->params = $temp;
		}

		return true;
	}
}
