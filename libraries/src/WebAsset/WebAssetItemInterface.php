<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

/**
 * Web Asset Item interface
 *
 * @since  __DEPLOY_VERSION__
 */
interface WebAssetItemInterface
{
	/**
	 * Return Asset name
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName(): string;

	/**
	 * Return Asset version
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getVersion();

	/**
	 * Return dependencies list
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDependencies(): array;

	/**
	 * Set the desired weight for the Asset in Graph.
	 * Final weight will be calculated by AssetManager according to dependency Graph.
	 *
	 * @param   float  $weight  The asset weight
	 *
	 * @return  self
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setWeight(float $weight): self;

	/**
	 * Return the weight of the Asset in Graph.
	 *
	 * @return  float
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getWeight(): float;

	/**
	 * Get CSS files
	 *
	 * @param   boolean  $resolvePath  Whether need to search for a real paths
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getStylesheetFiles($resolvePath = true): array;

	/**
	 * Get JS files
	 *
	 * @param   boolean  $resolvePath  Whether we need to search for a real paths
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getScriptFiles($resolvePath = true): array;

}
