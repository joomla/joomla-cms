<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;

/**
 * Defines the trait for a MVC factory factory aware class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait MVCFactoryFactoryAwareTrait
{
	/**
	 * The MVC Factory.
	 *
	 * @var MVCFactoryFactoryInterface
	 */
	private $mvcFactoryFactory;

	/**
	 * Creates an MVCFactory for the given application.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  MVCFactoryInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function createMVCFactory(CMSApplicationInterface $application): MVCFactoryInterface
	{
		return $this->getMVCFactoryFactory()->createFactory($application);
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
	public function setMvcFactoryFactory(MVCFactoryFactoryInterface $mvcFactoryFactory)
	{
		$this->mvcFactoryFactory = $mvcFactoryFactory;
	}

	/**
	 * Get the factory.
	 *
	 * @return  MVCFactoryFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException May be thrown if the factory has not been set.
	 */
	public function getMVCFactoryFactory()
	{
		if (!$this->mvcFactoryFactory)
		{
			throw new \UnexpectedValueException('MVC factory factory not set in ' . __CLASS__);
		}

		return $this->mvcFactoryFactory;
	}
}
