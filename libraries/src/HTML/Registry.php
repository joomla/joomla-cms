<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML;

defined('JPATH_PLATFORM') or die;

/**
 * Service registry for JHtml services
 *
 * @since  4.0.0
 */
final class Registry
{
	/**
	 * Array holding the registered services
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $serviceMap = [
		'access'          => \JHtmlAccess::class,
		'batch'           => \JHtmlBatch::class,
		'behavior'        => \JHtmlBehavior::class,
		'bootstrap'       => \JHtmlBootstrap::class,
		'category'        => \JHtmlCategory::class,
		'content'         => \JHtmlContent::class,
		'contentlanguage' => \JHtmlContentlanguage::class,
		'date'            => \JHtmlDate::class,
		'debug'           => \JHtmlDebug::class,
		'draggablelist'   => \JHtmlDraggablelist::class,
		'dropdown'        => \JHtmlDropdown::class,
		'email'           => \JHtmlEmail::class,
		'form'            => \JHtmlForm::class,
		'formbehavior'    => \JHtmlFormbehavior::class,
		'grid'            => \JHtmlGrid::class,
		'icons'           => \JHtmlIcons::class,
		'jgrid'           => \JHtmlJGrid::class,
		'jquery'          => \JHtmlJquery::class,
		'links'           => \JHtmlLinks::class,
		'list'            => \JHtmlList::class,
		'menu'            => \JHtmlMenu::class,
		'number'          => \JHtmlNumber::class,
		'searchtools'     => \JHtmlSearchtools::class,
		'select'          => \JHtmlSelect::class,
		'sidebar'         => \JHtmlSidebar::class,
		'sortablelist'    => \JHtmlSortablelist::class,
		'string'          => \JHtmlString::class,
		'tag'             => \JHtmlTag::class,
		'tel'             => \JHtmlTel::class,
		'user'            => \JHtmlUser::class,
	];

	/**
	 * Class loader method
	 *
	 * Additional arguments may be supplied and are passed to the sub-class.
	 * Additional include paths are also able to be specified for third-party use
	 *
	 * @param   string  $key         The name of helper method to load, service.function.
	 * @param   array   $methodArgs  The arguments to pass forward to the method being called
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 */
	public function _(string $key, ...$methodArgs)
	{
		$parts = explode('.', $key);

		// Last element is the function
		$function = array_pop($parts);

		// The rest is the service
		$service = implode('.', $parts);

		if (!$this->hasService($service))
		{
			return HTMLHelper::_($key, $methodArgs);
		}

		$toCall = [$this->getService($service), $function];

		if (!is_callable($toCall))
		{
			throw new \InvalidArgumentException(sprintf('%s::%s not found.', $service, $function), 500);
		}

		return call_user_func_array($toCall, $methodArgs);
	}

	/**
	 * Get the service for a given key
	 *
	 * @param   string  $key  The service key to look up
	 *
	 * @return  string|object
	 *
	 * @since   4.0.0
	 */
	public function getService(string $key)
	{
		if (!$this->hasService($key))
		{
			throw new \InvalidArgumentException("The '$key' service key is not registered.");
		}

		return $this->serviceMap[$key];
	}

	/**
	 * Check if the registry has a service for the given key
	 *
	 * @param   string  $key  The service key to look up
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function hasService(string $key): bool
	{
		return isset($this->serviceMap[$key]);
	}

	/**
	 * Register a service
	 *
	 * @param   string         $key      The service key to be registered
	 * @param   string|object  $handler  The handler for the service as either a PHP class name or class object
	 * @param   boolean        $replace  Flag indicating the service key may replace an existing definition
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(string $key, $handler, bool $replace = false)
	{
		// If the key exists already and we aren't instructed to replace existing services, bail early
		if (isset($this->serviceMap[$key]) && !$replace)
		{
			throw new \RuntimeException("The '$key' service key is already registered.");
		}

		// If the handler is a string, it must be a class that exists
		if (is_string($handler) && !class_exists($handler))
		{
			throw new \RuntimeException("The '$handler' class for service key '$key' does not exist.");
		}

		// Otherwise the handler must be a class object
		if (!is_string($handler) && !is_object($handler))
		{
			throw new \RuntimeException(
				sprintf(
					'The handler for service key %1$s must be a PHP class name or class object, a %2$s was given.',
					$key,
					gettype($handler)
				)
			);
		}

		$this->serviceMap[$key] = $handler;
	}
}
