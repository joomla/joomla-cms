<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Trait to implement AssociationServiceInterface
 *
 * @since  4.0.0
 */
trait RouterServiceTrait
{
    /**
     * The router factory.
     *
     * @var RouterFactoryInterface
     *
     * @since  4.0.0
     */
    private $routerFactory = null;

    /**
     * Returns the router.
     *
     * @param   CMSApplicationInterface  $application  The application object
     * @param   AbstractMenu             $menu         The menu object to work with
     *
     * @return  RouterInterface
     *
     * @since  4.0.0
     */
    public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface
    {
        return $this->routerFactory->createRouter($application, $menu);
    }

    /**
     * The router factory.
     *
     * @param   RouterFactoryInterface  $routerFactory  The router factory
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function setRouterFactory(RouterFactoryInterface $routerFactory)
    {
        $this->routerFactory = $routerFactory;
    }
}
