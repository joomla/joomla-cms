<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

/**
 * An interface for applications which can temporarily freeze loading any plugins.
 *
 * @since  __DEPLOY_VERSION__
 */
interface PluginFreezingApplicationInterface
{
    /**
     * Is loading plugins temporarily frozen?
     *
     * @return  bool
     * @since   __DEPLOY_VERSION__
     */
    public function isPluginLoadingFrozen(): bool;
}
