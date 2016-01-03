<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML Asset helper.
 */
class JHtmlAsset
{
	/**
	 * Active AssetFactory instance
	 * @var JHtmlAssetFactory
	 */
	protected static $instance;

	/**
	 * Set up and return AssetFactory instance
	 * @return JHtmlAssetFactory
	 */
	public static function instance()
	{
		if (!static::$instance)
		{
			static::$instance = new JHtmlAssetFactory;
		}

		return static::$instance;
	}
}
