<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Fields\Administrator\Extension\FieldsComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The fields service provider.
 *
 * @since  4.0.0
 */
return new class () implements ServiceProviderInterface {
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
        $container->registerServiceProvider(new CategoryFactory('\\Joomla\\Component\\Fields'));
        $container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Fields'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Fields'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new FieldsComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setCategoryFactory($container->get(CategoryFactoryInterface::class));

                return $component;
            }
        );
    }
};
