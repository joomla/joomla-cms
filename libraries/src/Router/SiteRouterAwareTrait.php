<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

/**
 * Defines the trait for a Site Router Aware Class.
 *
 * @since  4.2.0
 */
trait SiteRouterAwareTrait
{
    /**
     * @var    SiteRouter
     * @since  4.2.0
     */
    private $router;

    /**
     * Get the site router.
     *
     * @return  SiteRouter
     *
     * @since   4.2.0
     *
     * @throws  \UnexpectedValueException May be thrown if the router has not been set.
     */
    public function getSiteRouter(): SiteRouter
    {
        if ($this->router) {
            return $this->router;
        }

        throw new \UnexpectedValueException('SiteRouter not set in ' . __CLASS__);
    }

    /**
     * Set the router to use.
     *
     * @param   SiteRouter  $router  The router to use.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function setSiteRouter(SiteRouter $router): void
    {
        $this->router = $router;
    }
}
