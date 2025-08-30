<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Joomla Auto Update events
 *
 * @since   __DEPLOY_VERSION__
 */
class BeforeJoomlaAutoupdateEvent extends AbstractJoomlaUpdateEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        $arguments['stoppedUpdate'] = false;

        parent::__construct($name, $arguments);
    }

    /**
     * Set stop parameter to true
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function stopUpdate()
    {
        $this->arguments['stoppedUpdate'] = true;
        $this->stopPropagation();
    }
}
