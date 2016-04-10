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

	/**
	 * Adds a script to the page
	 *
	 * @param   string  $content  Script
	 * @param   array   $deps     The script dependancy
	 * @param   string  $type     Scripting mime (defaults to 'text/javascript')
	 *
	 * @return void
	 */
	public static function scriptDeclaration($content, array $deps = array(), $type = 'text/javascript')
	{
		static $loaded = array();
		$key = md5($content);

		// Avoid duplication
		if (!empty($loaded[$key]))
		{
			return;
		}

		// Load dependancy
		if (!empty($deps))
		{
			foreach($deps as $dep) {
				static::load($dep);
			}
		}

		// Attach the script to the Document
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($content, $type);

		$loaded[$key] = true;
	}

	/**
	 * Adds a stylesheet declaration to the page
	 *
	 * @param   string  $content  Style declarations
	 * @param   array   $deps     The style dependancy
	 * @param   string  $type     Type of stylesheet (defaults to 'text/css')
	 *
	 * @return void
	 */
	public static function styleDeclaration($content, array $deps = array(), $type = 'text/css')
	{
		static $loaded = array();
		$key = md5($content);

		// Avoid duplication
		if (!empty($loaded[$key]))
		{
			return;
		}

		// Load dependancy
		if (!empty($deps))
		{
			foreach($deps as $dep) {
				static::load($dep);
			}
		}

		// Attach the script to the Document
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration($content, $type);

		$loaded[$key] = true;
	}
}
