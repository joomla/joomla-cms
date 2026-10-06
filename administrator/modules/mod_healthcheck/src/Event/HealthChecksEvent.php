<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_healthcheck
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Healthcheck\Administrator\Event;

use Joomla\CMS\Event\AbstractEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event object for retrieving pluggable health checks
 *
 * @since  __DEPLOY_VERSION__
 */
class HealthChecksEvent extends AbstractEvent
{
    /**
     * The event context
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $context;

    /**
     * Get the event context
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the event context
     *
     * @param   string  $context  The event context
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $context;
    }
}
