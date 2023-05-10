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
 * Namespace based implementation of the ModuleDispatcherFactoryInterface
 *
 * @since  4.0.0
 */
class ModuleDispatcherFactory implements ModuleDispatcherFactoryInterface
{
    /**
     * The extension namespace
     *
     * @var  string
     *
     * @since   4.0.0
     */
    private $namespace;

    /**
     * ModuleDispatcherFactory constructor.
     *
     * @param   string  $namespace  The namespace
     *
     * @since   4.0.0
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Creates a dispatcher.
     *
     * @param   \stdClass                $module       The module
     * @param   CMSApplicationInterface  $application  The application
     * @param   Input                    $input        The input object, defaults to the one in the application
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     */
    public function createDispatcher(\stdClass $module, CMSApplicationInterface $application, Input $input = null): DispatcherInterface
    {
        $name = 'Site';

        if ($application->isClient('administrator')) {
            $name = 'Administrator';
        }

        $className = '\\' . trim($this->namespace, '\\') . '\\' . $name . '\\Dispatcher\\Dispatcher';

        if (!class_exists($className)) {
            $className = ModuleDispatcher::class;
        }

        return new $className($module, $application, $input ?: $application->input);
    }
}
