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
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Dispatcher\DispatcherInterface;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class Component implements ComponentInterface
{
	/**
	 * An array of categories.
	 *
	 * @var array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $categories;

	/**
	 * The site dispatcher.
	 *
	 * @var DispatcherInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $siteDispatcher;

	/**
	 * The admin dispatcher.
	 *
	 * @var DispatcherInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $adminDispatcher;

	/**
	 * Returns the dispatcher for the given application, null if none exists.
	 *
	 * @return  DispatcherInterface|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDispatcher(CMSApplicationInterface $application)
	{
		if ($application->isClient('site'))
		{
			return $this->siteDispatcher;
		}

		if ($application->isClient('administrator'))
		{
			return $this->adminDispatcher;
		}

		return null;
	}

	/**
	 * Sets the site dispatcher.
	 *
	 * @param    DispatcherInterface  $siteDispatcher  The dispatcher
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setSiteDispatcher(DispatcherInterface $siteDispatcher)
	{
		$this->siteDispatcher = $siteDispatcher;
	}

	/**
	 * Sets the admin dispatcher.
	 *
	 * @param    DispatcherInterface  $administratorDispatcher  The dispatcher
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setAdministratorDispatcher(DispatcherInterface $administratorDispatcher)
	{
		$this->adminDispatcher = $administratorDispatcher;
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
}
