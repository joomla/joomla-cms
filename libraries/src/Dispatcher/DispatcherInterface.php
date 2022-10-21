<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Platform CMS Dispatcher Interface
 *
 * @since  4.0.0
 */
interface DispatcherInterface
{
    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function dispatch();
}
