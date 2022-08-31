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

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The component router service.
 *
 * @since  4.0.0
 */
interface RouterServiceInterface
{
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
    public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface;
}
