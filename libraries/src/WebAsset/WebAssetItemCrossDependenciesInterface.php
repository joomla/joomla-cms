<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for Web Asset Item with cross dependencies
 *
 * Asset Item are "read only" object, all properties must be set through class constructor.
 *
 * @since  __DEPLOY_VERSION__
 */
interface WebAssetItemCrossDependenciesInterface
{
    /**
     * Return associative list of cross dependencies.
     * Example: ['script' => ['script1', 'script2'], 'style' => ['style1', 'style2']]
     *
     * @return  array[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getCrossDependencies(): array;
}
