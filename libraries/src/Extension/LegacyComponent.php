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
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoriesServiceInterface;
use Joomla\CMS\Categories\SectionNotFoundException;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Dispatcher\LegacyDispatcher;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;

/**
 * Access to component specific services.
 *
 * @since  4.0.0
 */
class LegacyComponent implements ComponentInterface, MVCFactoryServiceInterface, CategoriesServiceInterface, FieldsServiceInterface
{
	/**
	 * @var string
	 *
	 * @since  4.0.0
	 */
	private $component;

	/**
	 * LegacyComponentContainer constructor.
	 *
	 * @param   string  $component  The component
	 *
	 * @since  4.0.0
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
	 * @since   4.0.0
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
	 * @since  4.0.0
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
	 * Returns the category service.
	 *
	 * @param   array   $options  The options
	 * @param   string  $section  The section
	 *
	 * @return  Categories
	 *
	 * @see Categories::setOptions()
	 *
	 * @since   4.0.0
	 * @throws  SectionNotFoundException
	 */
	public function getCategories(array $options = [], $section = ''): Categories
	{
		$classname = ucfirst($this->component) . ucfirst($section) . 'Categories';

		if (!class_exists($classname))
		{
			$path = JPATH_SITE . '/components/com_' . $this->component . '/helpers/category.php';

			if (!is_file($path))
			{
				throw new SectionNotFoundException;
			}

			include_once $path;
		}

		if (!class_exists($classname))
		{
			throw new SectionNotFoundException;
		}

		return new $classname($options);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   \stdClass[]  $items    The category objects
	 * @param   string       $section  The section
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function countItems(array $items, string $section)
	{
		$helper = $this->loadHelper();

		if (!$helper || !is_callable(array($helper, 'countItems')))
		{
			return;
		}

		$helper::countItems($items, $section);
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   \stdClass[]  $items      The content objects
	 * @param   string       $extension  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function countTagItems(array $items, string $extension)
	{
		$helper = $this->loadHelper();

		if (!$helper || !is_callable(array($helper, 'countTagItems')))
		{
			return;
		}

		$helper::countTagItems($items, $extension);
	}

	/**
	 * Returns a valid section for articles. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 * @param   object  $item     The item
	 *
	 * @return  string|null  The new section
	 *
	 * @since   4.0.0
	 */
	public function validateSection($section, $item = null)
	{
		$helper = $this->loadHelper();

		if (!$helper || !is_callable(array($helper, 'validateSection')))
		{
			return $section;
		}

		return $helper::validateSection($section, $item);
	}

	/**
	 * Returns valid contexts.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getContexts(): array
	{
		$helper = $this->loadHelper();

		if (!$helper || !is_callable(array($helper, 'getContexts')))
		{
			return [];
		}

		return $helper::getContexts();
	}

	/**
	 * Returns the classname of the legacy helper class. If none is found it returns false.
	 *
	 * @return  bool|string
	 *
	 * @since   4.0.0
	 */
	private function loadHelper()
	{
		$className = ucfirst($this->component) . 'Helper';

		if (class_exists($className))
		{
			return $className;
		}

		$file = \JPath::clean(JPATH_ADMINISTRATOR . '/components/com_' . $this->component . '/helpers/' . $this->component . '.php');

		if (!file_exists($file))
		{
			return false;
		}

		\JLoader::register($className, $file);

		if (!class_exists($className))
		{
			return false;
		}

		return $className;
	}
}
