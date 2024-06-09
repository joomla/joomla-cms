<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

use Psr\Container\ContainerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for lazy subscribers
 *
 * @since  __DEPLOY_VERSION__
 */
class LazyServiceEventSubscriber
{
    /**
     * The service container
     *
     * @var    ContainerInterface
     * @since  __DEPLOY_VERSION__
     */
    private $container;

    /**
     * Listener class name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $class;

    /**
     * Constructor.
     *
     * @param \Psr\Container\ContainerInterface $container  Container
     * @param string                            $class      Listener class name
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(ContainerInterface $container, string $class)
    {
        $this->container = $container;
        $this->class     = $class;
    }

}
