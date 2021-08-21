<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Router factory interface
 *
 * @since  4.0.0
 */
interface RouterFactoryInterface
{
	/**
	 * Creates a router.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 * @param   AbstractMenu             $menu         The menu object to work with
	 *
	 * @return  RouterInterface
	 *
	 * @since   4.0.0
	 */
	public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface;
}
