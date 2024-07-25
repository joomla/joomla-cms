<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component dispatcher factory interface
 *
 * @since  4.0.0
 */
interface ComponentDispatcherFactoryInterface
{
    /**
     * Creates a dispatcher.
     *
     * @param   CMSApplicationInterface  $application  The application
     * @param   Input                    $input        The input object, defaults to the one in the application
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     */
    public function createDispatcher(CMSApplicationInterface $application, Input $input = null): DispatcherInterface;
}
