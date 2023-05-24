<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Placeholder plugin. The Plugin does not provide any events.
 *
 * @since  4.0.0
 */
class DummyPlugin extends CMSPlugin
{
    /**
     * Override parent constructor, to keep it clean.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct()
    {
        // The plugin does not provide any events
    }

    /**
     * Override parent registerListeners, to keep it clean.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function registerListeners()
    {
        // The plugin does not provide any events
    }
}
