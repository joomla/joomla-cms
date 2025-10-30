<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Behaviour.compat6
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Behaviour\Compat6\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Compat6 Plugin.
 *
 * @since  5.4.0
 */
final class Compat6 extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of CMS events this plugin will listen to and the respective handlers.
     *
     * @return  array
     *
     * @since  5.4.0
     */
    public static function getSubscribedEvents(): array
    {
        /**
         * This plugin does not listen to any events.
         */
        return [];
    }
}
