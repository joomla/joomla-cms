<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class Component implements ComponentInterface
{
	/**
	 * The MVC Factory.
	 *
	 * @var MVCFactoryFactoryInterface
	 */
	private $mvcFactoryFactory;

	/**
	 * An array of categories.
	 *
	 * @var array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $categories;

	/**
	 * The dispatcher factory.
	 *
	 * @var DispatcherFactoryInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $dispatcherFactory;

	/**
	 * The container for the additional services.
	 *
	 * @var ContainerInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $container;

	/**
	 * Component constructor.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Returns the dispatcher for the given application.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDispatcher(CMSApplicationInterface $application): DispatcherInterface
	{
		if ($this->dispatcherFactory === null)
		{
			return null;
		}

		return $this->dispatcherFactory->createDispatcher($application);
	}

	/**
	 * Sets the dispatcher factory.
	 *
	 * @param   DispatcherFactoryInterface  $dispatcherFactory  The dispatcher factory
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDispatcherFactory(DispatcherFactoryInterface $dispatcherFactory)
	{
		$this->dispatcherFactory = $dispatcherFactory;
	}

	/**
	 * Returns an MVCFactory.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  MVCFactoryInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function createMVCFactory(CMSApplicationInterface $application): MVCFactoryInterface
	{
		if ($this->mvcFactoryFactory === null)
		{
			return null;
		}

		return $this->mvcFactoryFactory->createFactory($application);
	}

	/**
	 * The MVC Factory to create MVCFactories from.
	 *
	 * @param   MVCFactoryFactoryInterface  $mvcFactoryFactory  The factory
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setMvcFactory(MVCFactoryFactoryInterface $mvcFactoryFactory)
	{
		$this->mvcFactoryFactory = $mvcFactoryFactory;
	}

	/**
	 * Returns the category service. If the service is not available
	 * null is returned.
	 *
	 * @param   array   $options  The options
	 * @param   string  $section  The section
	 *
	 * @return  Categories|null
	 *
	 * @see Categories::setOptions()
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getCategories(array $options = [], $section = '')
	{
		if (!array_key_exists($section, $this->categories))
		{
			return null;
		}

		$categories = clone $this->categories[$section];
		$categories->setOptions($options);

		return $categories;
	}

	/**
	 * An array of categories where the key is the name of the section.
	 * If the component has no sections then the array must have at least
	 * an empty key.
	 *
	 * @param   array  $categories  The categories
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setCategories(array $categories)
	{
		$this->categories = $categories;
	}

	/**
	 * Returns a service for the given key.
	 *
	 * @param   string  $key  The key
	 *
	 * @return  mixed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getService(string $key)
	{
		if (!$this->container->has($key))
		{
			return null;
		}

		return $this->container->get($key);
	}
}
