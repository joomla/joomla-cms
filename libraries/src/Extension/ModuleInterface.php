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
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Access to module specific services.
 *
 * @since  4.0.0
 */
interface ModuleInterface
{
    /**
     * Returns the dispatcher for the given application, module and input.
     *
     * @param   \stdClass                $module       The module
     * @param   CMSApplicationInterface  $application  The application
     * @param   ?Input                   $input        The input object, defaults to the one in the application
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     */
    public function getDispatcher(\stdClass $module, CMSApplicationInterface $application, ?Input $input = null): DispatcherInterface;
}
