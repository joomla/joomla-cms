<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_version
 *
 * @since  1.6
 */
abstract class ModVersionHelper
{
	/**
	 * Get the member items of the submenu.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The parameters object.
	 *
	 * @return  string  String containing the current Joomla version based on the selected format.
	 */
	public static function getVersion(&$params)
	{
		$version     = new JVersion;
		$versionText = $version->getShortVersion();
		$product     = $params->get('product', 0);

		if ($params->get('format', 'short') === 'long')
		{
			$versionText = str_replace($version::PRODUCT . ' ', '', $version->getLongVersion());
		}

		if (!empty($product))
		{
			$versionText = $version::PRODUCT . ' ' . $versionText;
		}

		return $versionText;
	}
}
