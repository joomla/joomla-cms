<?php
/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

/**
 * Helper methods for media web service.
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaHelper
{
	/**
	 * Split a given path in adapter prefix and file path.
	 *
	 * @param   string  $path  The path to split.
	 *
	 * @return  array   An array with elements 'adapter' and 'path'.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function adapterNameAndPath(String $path)
	{
		$result = [];
		$parts = explode(':', $path, 2);

		// If we have 2 parts, we have both an adapter name and a file path.
		if (count($parts) == 2)
		{
			$result['adapter'] = $parts[0];
			$result['path'] = $parts[1];

			return $result;
		}

		// If we have less than 2 parts, we return a default adapter name.
		$result['adapter'] = self::defaultAdapterName();

		// If we have 1 part, we return it as the path. Otherwise we return a default path.
		$result['path'] = count($parts) ? $parts[0] : '/';

		return $result;
	}

	/**
	 * Returns the default adapter name.
	 *
	 * @return  string   The adapter name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private static function defaultAdapterName(): string
	{
		static $comMediaParams;

		if (!$comMediaParams)
		{
			$comMediaParams = ComponentHelper::getParams('com_media');
		}

		return 'local-' . $comMediaParams->get('file_path', 'images');
	}
}
