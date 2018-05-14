<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;

/**
 * Access to component specific services.
 *
 * @since  4.0.0
 */
class Component implements ComponentInterface
{
	/**
	 * The dispatcher factory.
	 *
	 * @var DispatcherFactoryInterface
	 *
	 * @since  4.0.0
	 */
	private $dispatcherFactory;

	/**
	 * Returns the dispatcher for the given application.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   4.0.0
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
	 * @since   4.0.0
	 */
	public function setDispatcherFactory(DispatcherFactoryInterface $dispatcherFactory)
	{
		$this->dispatcherFactory = $dispatcherFactory;
	}
}
