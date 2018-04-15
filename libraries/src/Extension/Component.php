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
use Joomla\CMS\Categories\CategoryAwareInterface;
use Joomla\CMS\Categories\CategoryAwareTrait;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class Component implements ComponentInterface, CategoryAwareInterface
{
	use CategoryAwareTrait;

	/**
	 * The MVC Factory.
	 *
	 * @var MVCFactoryFactoryInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $mvcFactoryFactory;

	/**
	 * The dispatcher factory.
	 *
	 * @var DispatcherFactoryInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $dispatcherFactory;

	/**
	 * The association extension.
	 *
	 * @var AssociationExtensionInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $associationExtension;

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
	 * Returns the associations helper.
	 *
	 * @return  AssociationExtensionInterface|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAssociationsExtension()
	{
		return $this->associationExtension;
	}

	/**
	 * The association extension.
	 *
	 * @param   AssociationExtensionInterface  $associationExtension  The extension
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setAssociationExtension(AssociationExtensionInterface $associationExtension)
	{
		$this->associationExtension = $associationExtension;
	}
}
