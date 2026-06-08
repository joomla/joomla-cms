<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Namespace based implementation of the ComponentDispatcherFactoryInterface
 *
 * @since  4.0.0
 */
class ComponentDispatcherFactory implements ComponentDispatcherFactoryInterface
{
    /**
     * The extension namespace
     *
     * @var  string
     *
     * @since   4.0.0
     */
    protected $namespace;

    /**
     * The MVC factory
     *
     * @var  MVCFactoryInterface
     *
     * @since   4.0.0
     */
    private $mvcFactory;

    /**
     * ComponentDispatcherFactory constructor.
     *
     * @param   string               $namespace   The namespace
     * @param   MVCFactoryInterface  $mvcFactory  The MVC factory
     *
     * @since   4.0.0
     */
    public function __construct(string $namespace, MVCFactoryInterface $mvcFactory)
    {
        $this->namespace  = $namespace;
        $this->mvcFactory = $mvcFactory;
    }

    /**
     * Creates a dispatcher.
     *
     * @param   CMSApplicationInterface  $application  The application
     * @param   ?Input                   $input        The input object, defaults to the one in the application
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     */
    public function createDispatcher(CMSApplicationInterface $application, ?Input $input = null): DispatcherInterface
    {
        $name = ucfirst($application->getName());

        $className = '\\' . trim($this->namespace, '\\') . '\\' . $name . '\\Dispatcher\\Dispatcher';

        if (!class_exists($className)) {
            if ($application->isClient('api')) {
                $className = ApiDispatcher::class;
            } else {
                $className = ComponentDispatcher::class;
            }
        }

        return new $className($application, $input ?: $application->getInput(), $this->mvcFactory);
    }
}
