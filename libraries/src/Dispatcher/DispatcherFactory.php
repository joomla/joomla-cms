<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;

/**
 * Namesapce based implementation of the DispatcherFactoryInterface
 *
 * @since  __DEPLOY_VERSION__
 */
class DispatcherFactory implements DispatcherFactoryInterface
{
	/**
	 * The extension namespace
	 *
	 * @var  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $namespace;

	/**
	 * The MVC factory
	 *
	 * @var  MVCFactoryFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $mvcFactory;

	/**
	 * DispatcherFactory constructor.
	 *
	 * @param   string                      $namespace   The namespace
	 * @param   MVCFactoryFactoryInterface  $mvcFactory  The MVC factory
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $namespace, MVCFactoryFactoryInterface $mvcFactory)
	{
		$this->namespace  = $namespace;
		$this->mvcFactory = $mvcFactory;
	}

	/**
	 * Creates a dispatcher.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createDispatcher(CMSApplicationInterface $application): DispatcherInterface
	{
		$name = 'Site';

		if ($application->isClient('administrator'))
		{
			$name = 'Administrator';
		}

		$className = '\\' . trim($this->namespace, '\\') . '\\' . $name . '\\Dispatcher\\Dispatcher';

		return new $className($application, $application->input, $this->mvcFactory);
	}
}
