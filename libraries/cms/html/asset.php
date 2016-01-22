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
 *
 * @since  5.0
 */
class JHtmlAsset
{
	/**
	 * Make the asset active
	 *
	 * @param   string|JAssetItem  $asset  Asset instance or name
	 *
	 * @return void
	 */
	public static function load($asset)
	{
		$name    = $asset;
		$factory = JAssetFactory::getInstance();

		if ($asset instanceof JAssetItem)
		{
			$name = $asset->getName();
			$factory->addAsset($asset);
		}

		$factory->setAssetState($name, JAssetItem::ASSET_STATE_ACTIVE);
	}

	/**
	 * Make the asset inactive
	 *
	 * @param   string|JAssetItem  $asset  Asset instance or name
	 *
	 * @return void
	 */
	public static function unload($asset)
	{
		$name = ($asset instanceof JAssetItem) ? $asset->getName() : $asset;

		JAssetFactory::getInstance()->setAssetState($name, JAssetItem::ASSET_STATE_INACTIVE);
	}

	/**
	 * Add asset to the collection of known assets
	 *
	 * @param   JAssetItem  $asset  Asset instance
	 *
	 * @return void
	 */
	public static function add(JAssetItem $asset)
	{
		JAssetFactory::getInstance()->addAsset($asset);
	}
}
