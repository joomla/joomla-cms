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
use Joomla\CMS\Categories\CategoryAwareInterface;
use Joomla\CMS\Categories\CategoryNotFoundExceptionInterface;
use Joomla\CMS\Categories\Exception\CategoriesNotImplementedException;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Dispatcher\LegacyDispatcher;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyComponent implements ComponentInterface, CategoryAwareInterface
{
	/**
	 * @var string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $component;

	/**
	 * LegacyComponentContainer constructor.
	 *
	 * @param   string  $component  The component
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(string $component)
	{
		$this->component = str_replace('com_', '', $component);
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
		return new LegacyDispatcher($application);
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
		// Will be removed when all extensions are converted to service providers
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_' . $this->component . '/dispatcher.php'))
		{
			return new MVCFactory('\\Joomla\\Component\\' . ucfirst($this->component), $application);
		}

		return new LegacyFactory;
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
	 * @throws CategoryNotFoundExceptionInterface
	 */
	public function getCategories(array $options = [], $section = ''): Categories
	{
		$classname = ucfirst($this->component) . ucfirst($section) . 'Categories';

		if (!class_exists($classname))
		{
			$path = JPATH_SITE . '/components/com_' . $this->component . '/helpers/category.php';

			if (!is_file($path))
			{
				throw new CategoriesNotImplementedException;
			}

			include_once $path;
		}

		if (!class_exists($classname))
		{
			throw new CategoriesNotImplementedException;
		}

		return new $classname($options);
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
		$className = ucfirst($this->component) . 'AssociationsHelper';

		if (class_exists($className))
		{
			return new $className;
		}

		// Check if associations helper exists
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_' . $this->component . '/helpers/associations.php'))
		{
			return null;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_' . $this->component . '/helpers/associations.php';

		if (!class_exists($className))
		{
			return null;
		}

		// Return an instance of the helper class
		return new $className;
	}
}
