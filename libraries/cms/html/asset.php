<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 *
	 * @var  JHtmlAssetFactory
	 */
	protected static $instance;

	/**
	 * Set up and return AssetFactory instance
	 *
	 * @return  JHtmlAssetFactory
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
	 *
	 * @param  string|JHtmlAssetItem  $asset  Asset instance or name
	 *
	 * @return void
	 */
	public static function load($asset)
	{
		$name = $asset;

		if ($asset instanceof JHtmlAssetItem)
		{
			$name = $asset->getName();
			static::instance()->addAsset($asset);
		}

		static::instance()->setAssetState($name, JHtmlAssetItem::ASSET_STATE_ACTIVE);
	}

	/**
	 * Make the asset inactive
	 *
	 * @param  string|JHtmlAssetItem  $asset  Asset instance or name
	 *
	 * @return void
	 */
	public static function unload($asset)
	{
		$name = ($asset instanceof JHtmlAssetItem) ? $asset->getName() : $asset;

		static::instance()->setAssetState($name, JHtmlAssetItem::ASSET_STATE_INACTIVE);
	}

	/**
	 * Add asset to the collection of known assets
	 *
	 * @param  JHtmlAssetItem  $asset
	 *
	 * @return void
	 */
	public static function add(JHtmlAssetItem $asset)
	{
		static::instance()->addAsset($asset);
	}
}
