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
use Joomla\CMS\Dispatcher\ModuleDispatcherFactoryInterface;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Access to module specific services.
 *
 * @since  4.0.0
 */
class Module implements ModuleInterface, HelperFactoryInterface
{
    /**
     * The dispatcher factory.
     *
     * @var ModuleDispatcherFactoryInterface
     *
     * @since  4.0.0
     */
    private $dispatcherFactory;

    /**
     * The helper factory.
     *
     * @var HelperFactoryInterface
     *
     * @since  4.0.0
     */
    private $helperFactory;

    /**
     * Module constructor.
     *
     * @param   ModuleDispatcherFactoryInterface  $dispatcherFactory  The dispatcher factory
     * @param   HelperFactoryInterface            $helperFactory      The helper factory
     *
     * @since   4.0.0
     */
    public function __construct(ModuleDispatcherFactoryInterface $dispatcherFactory, ?HelperFactoryInterface $helperFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
        $this->helperFactory     = $helperFactory;
    }

    /**
     * Returns the dispatcher for the given application, module and input.
     *
     * @param   \stdClass                $module       The module
     * @param   CMSApplicationInterface  $application  The application
     * @param   Input                    $input        The input object, defaults to the one in the application
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     */
    public function getDispatcher(\stdClass $module, CMSApplicationInterface $application, Input $input = null): DispatcherInterface
    {
        $dispatcher = $this->dispatcherFactory->createDispatcher($module, $application, $input);

        if ($dispatcher instanceof HelperFactoryAwareInterface) {
            $dispatcher->setHelperFactory($this->helperFactory);
        }

        return $dispatcher;
    }

    /**
     * Returns a helper instance for the given name.
     *
     * @param   string  $name    The name
     * @param   array   $config  The config
     *
     * @return  \stdClass
     *
     * @since   4.0.0
     */
    public function getHelper(string $name, array $config = [])
    {
        return $this->helperFactory->getHelper($name, $config);
    }
}
