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
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Dispatcher\ModuleDispatcherInterface;

/**
 * Access to module specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class Module implements ModuleInterface
{
	/**
	 * The dispatcher factory.
	 *
	 * @var DispatcherFactoryInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $dispatcherFactory;

	/**
	 * Returns the dispatcher for the given application, null if none exists.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  ModuleDispatcherInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDispatcher(CMSApplicationInterface $application): ModuleDispatcherInterface
	{
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
}
