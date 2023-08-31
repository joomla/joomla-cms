<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Application;

use Joomla\DI\Container;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for BeforeExecute event
 *
 * @since  5.0.0
 */
class BeforeExecuteEvent extends ApplicationEvent
{
    /**
     * Get the event's container object
     *
     * @return  ?Container
     *
     * @since  5.0.0
     */
    public function getContainer(): ?Container
    {
        return $this->arguments['container'] ?? null;
    }
}
