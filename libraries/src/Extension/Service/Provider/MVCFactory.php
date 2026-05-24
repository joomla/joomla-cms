<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

use Joomla\CMS\Cache\CacheControllerFactoryAwareInterface;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Mail\MailerFactoryAwareInterface;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Factory\ApiMVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Router\SiteRouterAwareInterface;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the service MVC factory.
 *
 * @since  4.0.0
 */
class MVCFactory implements ServiceProviderInterface
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
     * MVCFactory constructor.
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
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function register(Container $container)
    {
        $container->set(
            MVCFactoryInterface::class,
            function (Container $container) {
                $factory = $this->createMVCFactory();

                $this->injectServicesIntoFactory($factory, $container);

                return $factory;
            }
        );
    }

    /**
     * Return component namespace
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Create MVC Factory
     *
     * @return  MVCFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createMVCFactory(): MVCFactoryInterface
    {
        if (Factory::getApplication()->isClient('api')) {
            return new ApiMVCFactory($this->namespace);
        }

        return new \Joomla\CMS\MVC\Factory\MVCFactory($this->namespace);
    }

    /**
     * Inject services from container into MVC Factory
     *
     * @param   MVCFactoryInterface  $factory    The MVC Factory
     * @param   Container            $container  The DI container
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function injectServicesIntoFactory(MVCFactoryInterface $factory, Container $container): void
    {
        if ($factory instanceof FormFactoryAwareInterface) {
            $factory->setFormFactory($container->get(FormFactoryInterface::class));
        }

        if ($factory instanceof DispatcherAwareInterface) {
            $factory->setDispatcher($container->get(DispatcherInterface::class));
        }

        if ($factory instanceof DatabaseAwareInterface) {
            $factory->setDatabase($container->get(DatabaseInterface::class));
        }

        if ($factory instanceof SiteRouterAwareInterface) {
            $factory->setSiteRouter($container->get(SiteRouter::class));
        }

        if ($factory instanceof CacheControllerFactoryAwareInterface) {
            $factory->setCacheControllerFactory($container->get(CacheControllerFactoryInterface::class));
        }

        if ($factory instanceof UserFactoryAwareInterface) {
            $factory->setUserFactory($container->get(UserFactoryInterface::class));
        }

        if ($factory instanceof MailerFactoryAwareInterface) {
            $factory->setMailerFactory($container->get(MailerFactoryInterface::class));
        }
    }
}
