<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;
use Joomla\CMS\WebAsset\Exception\UnsatisfiedDependencyException;
use Joomla\CMS\WebAsset\Exception\InvalidActionException;

/**
 * Web Asset Manager Interface
 *
 * @since  4.0.0
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
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  InvalidActionException When the Manager already attached to a Document
	 *
	 * @since  4.0.0
	 */
	public function enableAsset(string $name): self;

	/**
	 * Deactivate the Asset item
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return self
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  InvalidActionException When the Manager already attached to a Document
	 *
	 * @since  4.0.0
	 */
	public function disableAsset(string $name): self;

	/**
	 * Check whether the asset are enabled
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return  bool
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 *
	 * @since  4.0.0
	 */
	public function isAssetActive(string $name): bool;

	/**
	 * Get all assets that was enabled
	 *
	 * @param   bool  $sort  Whether need to sort the assets to follow the dependency Graph
	 *
	 * @return  WebAssetItemInterface[]
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  UnsatisfiedDependencyException When Dependency cannot be found
	 *
	 * @since  4.0.0
	 */
	public function getAssets(bool $sort = false): array;

	/**
	 * Attach active assets to the document
	 *
	 * @param   Document  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  self
	 *
	 * @throws InvalidActionException When the Manager already attached to a Document
	 *
	 * @since  4.0.0
	 */
	public function attachActiveAssetsToDocument(Document $doc): self;

}

