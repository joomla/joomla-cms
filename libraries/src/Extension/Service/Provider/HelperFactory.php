<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Helper\HelperFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the service helper factory.
 *
 * @since  4.0.0
 */
class HelperFactory implements ServiceProviderInterface
{
	/**
	 * The namespace
	 *
	 * @var  string
	 *
	 * @since   4.0.0
	 */
	private $namespace;

	/**
	 * HelperFactory constructor.
	 *
	 * @param   string  $namespace  The namespace
	 *
	 * @since   HelperFactory
	 */
	public function __construct(string $namespace)
	{
		$this->namespace = $namespace;
	}

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
		$container->set(
			HelperFactoryInterface::class,
			function (Container $container)
			{
				return new \Joomla\CMS\Helper\HelperFactory($this->namespace);
			}
		);
	}
}
