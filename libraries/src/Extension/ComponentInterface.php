<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;

/**
 * Access to component specific services.
 *
 * @since  4.0.0
 */
interface ComponentInterface
{
    /**
     * Returns the dispatcher for the given application.
     *
     * @param   CMSApplicationInterface  $application  The application
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     */
    public function getDispatcher(CMSApplicationInterface $application): DispatcherInterface;
}
