<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\DI\Container;
use Joomla\DI\ContainerAwareTrait;
use Joomla\DI\Exception\ContainerNotFoundException;

/**
 * Trait for classes which can load extensions
 *
 * @since  __DEPLOY_VERSION__
 */
trait ExtensionLoader
{

	/**
	 * Boots the component with the given name.
	 *
	 * @param   string  $component  The component to boot.
	 *
	 * @return  ComponentContainerInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function bootComponent($component): ComponentContainerInterface
	{
		// Normalize the component name
		$component = strtolower(str_replace('com_', '', $component));

		// Path to to look for services
		$path = JPATH_ADMINISTRATOR . '/components/com_' . $component;

		return $this->loadExtensionContainer(ucfirst($component) . 'ComponentContainer', 'com_' . $component, $path);
	}

	/**
	 * Loads the extension container for the given extension.
	 *
	 * @param   string  $serviceName    The service name
	 * @param   string  $extensionName  The extension name
	 * @param   string  $extensionPath  The path of the extension
	 *
	 * @return  mixed  The extension service container
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function loadExtensionContainer($serviceName, $extensionName, $extensionPath)
	{
		// The container which holds the component container
		$container = $this->getContainer();

		// Check if the service is already available
		if ($container->has($serviceName))
		{
			return $container->get($serviceName);
		}

		$path = $extensionPath . '/services/services.php';

		if (file_exists($path))
		{
			// Load the services file
			require_once $path;
		}

		// Fallback to legacy
		if (!$container->has($serviceName))
		{
			$legacyContainer = null;

			if (strpos($extensionName, 'com_') === 0)
			{
				$legacyContainer = new LegacyComponentContainer($extensionName);
			}

			$container->set($serviceName, $legacyContainer);
		}

		// Return the child container
		return $container->get($serviceName);
	}

	/**
	 * Get the DI container.
	 *
	 * @return  Container
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  ContainerNotFoundException May be thrown if the container has not been set.
	 */
	abstract protected function getContainer();
}
