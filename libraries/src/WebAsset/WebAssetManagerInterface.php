<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;

/**
 * Web Asset Manager Interface
 *
 * @since  __DEPLOY_VERSION__
 */
interface WebAssetManagerInterface
{
	/**
	 * Activate the Asset item
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function enableAsset(string $name): self;

	/**
	 * Deactivate the Asset item
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function disableAsset(string $name): self;

	/**
	 * Check whether the asset are enabled
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return  bool
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function isAssetActive(string $name): bool;

	/**
	 * Get all assets that was enabled
	 *
	 * @param   bool  $sort  Whether need to sort the assets to follow the dependency Graph
	 *
	 * @return  WebAssetItemInterface[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAssets(bool $sort = false): array;

	/**
	 * Attach active assets to the document
	 *
	 * @param   Document  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function attachActiveAssetsToDocument(Document $doc): self;

}

