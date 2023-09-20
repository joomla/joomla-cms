<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Behaviour.compat
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Behaviour\Compat\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Compat Plugin.
 *
 * @since  4.4.0
 */
final class Compat extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of CMS events this plugin will listen to and the respective handlers.
     *
     * @return  array
     *
     * @since  4.4.0
     */
    public static function getSubscribedEvents(): array
    {
        /**
         * This plugin does not listen to any events.
         */
        return [];
    }
}
