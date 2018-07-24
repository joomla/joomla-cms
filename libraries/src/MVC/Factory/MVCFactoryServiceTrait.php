<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;

/**
 * Defines the trait for a MVC factory factory service class.
 *
 * @since  4.0.0
 */
trait MVCFactoryServiceTrait
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
	 * @since  4.0.0
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
	 * @since  4.0.0
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
	 * @since   4.0.0
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
