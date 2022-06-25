<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

use Joomla\CMS\WebAsset\Exception\InvalidActionException;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;
use Joomla\CMS\WebAsset\Exception\UnsatisfiedDependencyException;

/**
 * Web Asset Manager Interface
 *
 * @since  4.0.0
 */
interface WebAssetManagerInterface
{
    /**
     * Enable an asset item to be attached to a Document
     *
     * @param   string  $type  Asset type, script or style etc
     * @param   string  $name  The asset name
     *
     * @return self
     *
     * @throws  UnknownAssetException  When Asset cannot be found
     * @throws  InvalidActionException When the Manager already attached to a Document
     *
     * @since  4.0.0
     */
    public function useAsset(string $type, string $name): self;

    /**
     * Deactivate an asset item, so it will not be attached to a Document
     *
     * @param   string  $type  Asset type, script or style etc
     * @param   string  $name  The asset name
     *
     * @return self
     *
     * @throws  UnknownAssetException  When Asset cannot be found
     * @throws  InvalidActionException When the Manager already attached to a Document
     *
     * @since  4.0.0
     */
    public function disableAsset(string $type, string $name): self;

    /**
     * Check whether the asset are enabled
     *
     * @param   string  $type  Asset type, script or style etc
     * @param   string  $name  The asset name
     *
     * @return  boolean
     *
     * @throws  UnknownAssetException  When Asset cannot be found
     *
     * @since  4.0.0
     */
    public function isAssetActive(string $type, string $name): bool;

    /**
     * Get all assets that was enabled for given type
     *
     * @param   string  $type  Asset type, script or style etc
     * @param   bool    $sort  Whether need to sort the assets to follow the dependency Graph
     *
     * @return  WebAssetItemInterface[]
     *
     * @throws  UnknownAssetException  When Asset cannot be found
     * @throws  UnsatisfiedDependencyException When Dependency cannot be found
     *
     * @since  4.0.0
     */
    public function getAssets(string $type, bool $sort = false): array;
}
