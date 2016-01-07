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
	 *
	 * @return JHtmlAssetFactory
	 *
	 * @TODO Move to JFactory
	 */
	public static function instance()
	{
		if (!static::$instance)
		{
			static::$instance = new JHtmlAssetFactory;
		}

		return static::$instance;
	}

	/**
	 * Make the asset active
	 * @param string $name assset name
	 * @return void
	 */
	public static function load($name)
	{
		static::instance()->makeActive($name, true);
	}

	/**
	 * Make the asset inactive
	 * @param string $name assset name
	 * @return void
	 */
	public static function unload($name)
	{
		static::instance()->makeActive($name, false);
	}

	/**
	 * Add asset to collection of known assets
	 * @param JHtmlAssetItem $asset
	 * @return void
	 */
	public static function add(JHtmlAssetItem $asset)
	{
		static::instance()->addAsset($asset);
	}
}
