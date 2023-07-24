<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Application;

use Joomla\DI\Container;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for BeforeExecute event
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeExecuteEvent extends ApplicationEvent
{
    /**
     * Get the event's container object
     *
     * @return  ?Container
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getContainer(): ?Container
    {
        return $this->arguments['container'] ?? null;
    }
}
