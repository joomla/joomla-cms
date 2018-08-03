<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;

/**
 * Namesapce based implementation of the DispatcherFactoryInterface
 *
 * @since  4.0.0
 */
class DispatcherFactory implements DispatcherFactoryInterface
{
	/**
	 * The extension namespace
	 *
	 * @var  string
	 *
	 * @since   4.0.0
	 */
	protected $namespace;

	/**
	 * The MVC factory
	 *
	 * @var  MVCFactoryFactoryInterface
	 *
	 * @since   4.0.0
	 */
	private $mvcFactoryFactory;

	/**
	 * DispatcherFactory constructor.
	 *
	 * @param   string                      $namespace          The namespace
	 * @param   MVCFactoryFactoryInterface  $mvcFactoryFactory  The MVC factory
	 *
	 * @since   4.0.0
	 */
	public function __construct(string $namespace, MVCFactoryFactoryInterface $mvcFactoryFactory)
	{
		$this->namespace         = $namespace;
		$this->mvcFactoryFactory = $mvcFactoryFactory;
	}

	/**
	 * Creates a dispatcher.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 * @param   Input                    $input        The input object, defaults to the one in the application
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   4.0.0
	 */
	public function createDispatcher(CMSApplicationInterface $application, Input $input = null): DispatcherInterface
	{
		$name = 'Site';

		if ($application->isClient('administrator'))
		{
			$name = 'Administrator';
		}

		$className = '\\' . trim($this->namespace, '\\') . '\\' . $name . '\\Dispatcher\\Dispatcher';

		if (!class_exists($className))
		{
			$className = '\\Joomla\\CMS\\Dispatcher\\Dispatcher';
		}

		return new $className($application, $input ?: $application->input, $this->mvcFactoryFactory);
	}
}
