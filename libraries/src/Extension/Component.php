<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;

/**
 * Access to component specific services.
 *
 * @since  4.0.0
 */
class Component implements ComponentInterface
{
    /**
     * Component constructor.
     *
     * @param   ComponentDispatcherFactoryInterface  $dispatcherFactory  The dispatcher factory
     *
     * @since   4.0.0
     */
    public function __construct(private readonly ComponentDispatcherFactoryInterface $dispatcherFactory)
    {
    }

    /**
     * Returns the dispatcher for the given application.
     *
     * @param   CMSApplicationInterface  $application  The application
     *
     *
     * @since   4.0.0
     */
    public function getDispatcher(CMSApplicationInterface $application): DispatcherInterface
    {
        return $this->dispatcherFactory->createDispatcher($application);
    }
}
