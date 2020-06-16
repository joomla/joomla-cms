<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Reporter component helper.
 *
 * @since  4.0.0
 */
class ReporterHelper
{
	/**
	 * Gets the httpheaders system plugin extension id.
	 *
	 * @return  mixed  The httpheaders system plugin extension id or false in case of an error.
	 *
	 * @since   4.0.0
	 */
	public static function getHttpHeadersPluginId()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('httpheaders'));
		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Check the com_csp trash to show a warning in this case
	 *
	 * @return  boolean  The status of the trash; Do items exists in the trash
	 *
	 * @since   4.0.0
	 */
	public static function getCspTrashStatus()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__csp'))
			->where($db->quoteName('published') . ' = ' . $db->quote('-2'));
		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return boolval($result);
	}

	/**
	 * Check whether there are unsafe-inline rules published
	 *
	 * @return  boolean  Whether there are unsafe-inline rules published
	 *
	 * @since   4.0.0
	 */
	public static function getCspUnsafeInlineStatus()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__csp'))
			->where($db->quoteName('blocked_uri') . ' = ' . $db->quote("'unsafe-inline'"))
			->where($db->quoteName('published') . ' = 1');
		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return boolval($result);
	}

	/**
	 * Check whether there are unsafe-eval rules published
	 *
	 * @return  boolean  Whether there are unsafe-eval rules published
	 *
	 * @since   4.0.0
	 */
	public static function getCspUnsafeEvalStatus()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__csp'))
			->where($db->quoteName('blocked_uri') . ' = ' . $db->quote("'unsafe-eval'"))
			->where($db->quoteName('published') . ' = 1');
		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return boolval($result);
	}
}
