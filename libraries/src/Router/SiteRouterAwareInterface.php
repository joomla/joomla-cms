<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

/**
 * Interface for site router aware classes.
 *
 * @since  4.2.0
 */
interface SiteRouterAwareInterface
{
    /**
     * Set the router to use.
     *
     * @param   SiteRouter  $router  The router to use.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function setSiteRouter(SiteRouter $router): void;
}
