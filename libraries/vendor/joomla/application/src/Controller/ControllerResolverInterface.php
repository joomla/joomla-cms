<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Controller;

use Joomla\Router\ResolvedRoute;

/**
 * Interface defining a controller resolver.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ControllerResolverInterface
{
	/**
	 * Resolve the controller for a route
	 *
	 * @param   ResolvedRoute  $route  The route to resolve the controller for
	 *
	 * @return  callable
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 */
	public function resolve(ResolvedRoute $route): callable;
}
