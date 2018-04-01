<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Factory;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Form\FormFactoryAwareTrait;

/**
 * Factory to create MVC factories.
 *
 * @since  __DEPLOY_VERSION__
 */
class MVCFactoryFactory implements MVCFactoryFactoryInterface, FormFactoryAwareInterface
{
	use FormFactoryAwareTrait;

	/**
	 * The namespace.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $namespace;

	/**
	 * The constructor.
	 *
	 * @param   string  $namespace  The extension namespace
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * Method to create a factory object.
	 *
	 * @param   CMSApplicationInterface  $application  The application.
	 *
	 * @return  \Joomla\CMS\MVC\Factory\MVCFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function createFactory(CMSApplicationInterface $application): MVCFactoryInterface
	{
		if (!$this->namespace)
		{
			return new LegacyFactory;
		}

		$factory = new MVCFactory($this->namespace, $application);

		try
		{
			$factory->setFormFactory($this->getFormFactory());
		}
		catch (\UnexpectedValueException $e)
		{
		}

		return $factory;
	}
}
