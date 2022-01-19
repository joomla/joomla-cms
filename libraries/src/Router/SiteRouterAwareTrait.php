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
 * Defines the trait for a Site Router Aware Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait SiteRouterAwareTrait
{
	/**
	 * @var    SiteRouter
	 * @since  __DEPLOY_VERSION__
	 */
	private $router;

	/**
	 * Get the site router.
	 *
	 * @return  SiteRouter
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  \UnexpectedValueException May be thrown if the router has not been set.
	 */
	public function getSiteRouter(): SiteRouter
	{
		if ($this->router)
		{
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function setSiteRouter(SiteRouter $router): void
	{
		$this->router = $router;
	}
}
