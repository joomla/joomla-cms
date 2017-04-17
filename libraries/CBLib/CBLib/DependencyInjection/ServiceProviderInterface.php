<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 12/5/13 1:41 AM $
* @package CBLib\DependencyInjection
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\DependencyInjection;

defined('CBLIB') or die();

/**
 * Defines the interface for a Service Provider to register to a Container.
 */
interface ServiceProviderInterface
{
	/**
	 * Registers the service provider to a DependencyInjection Container.
	 * (called by the Container $container's function registerServiceProvider( ServiceProviderInterface $provider ))
	 *
	 * @param   ContainerInterface  $container  DI container
	 * @return  Container                       For chaining.
	 */
	public function register( ContainerInterface $container );
}
