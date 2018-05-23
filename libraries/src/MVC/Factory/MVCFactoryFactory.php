<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Factory;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\CMS\HTML\HTMLRegistryAwareInterface;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;

/**
 * Factory to create MVC factories.
 *
 * @since  4.0.0
 */
class MVCFactoryFactory implements MVCFactoryFactoryInterface, FormFactoryAwareInterface
{
	use FormFactoryAwareTrait;
	use HTMLRegistryAwareTrait;

	/**
	 * The namespace.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	private $namespace;

	/**
	 * The constructor.
	 *
	 * @param   string  $namespace  The extension namespace
	 *
	 * @since   4.0.0
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
	 * @since   4.0.0
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

		try
		{
			$factory->setRegistry($this->getRegistry());
		}
		catch (\UnexpectedValueException $e)
		{
		}

		return $factory;
	}
}
