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
	 * @return  integer  The httpheaders system plugin extension id.
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
		}

		return boolval($result);
	}
}
