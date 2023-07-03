<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.compat
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Compat\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Compat Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
final class Compat extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of CMS events this plugin will listen to and the respective handlers.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        /**
         * Note that onAfterInitialise must be the first handlers to run for this
         * plugin to operate as expected. These handlers load compatibility code which
         * might be needed by other plugins
         */
        return [
            'onAfterInitialise' => ['eventAfterInitialise', Priority::HIGH],
        ];
    }

    /**
     * We run as early as possible, this should be the first event
     *
     * @param Event $event
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function eventAfterInitialise(Event $event)
    {
        /**
         * Load class names which are deprecated in joomla 4.0 and which will
         * likely be removed in Joomla 6.0
         */

        if ($this->params->get('classes_aliases')) {
            require_once dirname(__DIR__) . '/classmap/classmap.php';
        }
    }
}
