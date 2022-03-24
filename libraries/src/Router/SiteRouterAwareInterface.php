<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

\defined('_JEXEC') or die;

/**
 * Interface for site router aware classes.
 *
 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function setSiteRouter(SiteRouter $router): void;
}
