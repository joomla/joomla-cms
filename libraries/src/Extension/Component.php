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

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Access to component specific services.
 *
 * @since  4.0.0
 */
class Component implements ComponentInterface
{
    /**
     * The dispatcher factory.
     *
     * @var ComponentDispatcherFactoryInterface
     *
     * @since  4.0.0
     */
    private $dispatcherFactory;

    /**
     * Component constructor.
     *
     * @param   ComponentDispatcherFactoryInterface  $dispatcherFactory  The dispatcher factory
     *
     * @since   4.0.0
     */
    public function __construct(ComponentDispatcherFactoryInterface $dispatcherFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
    }

    /**
     * Returns the dispatcher for the given application.
     *
     * @param   CMSApplicationInterface  $application  The application
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     */
    public function getDispatcher(CMSApplicationInterface $application): DispatcherInterface
    {
        return $this->dispatcherFactory->createDispatcher($application);
    }
}
