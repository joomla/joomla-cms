<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\Categories;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyComponentContainer implements ComponentContainerInterface
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
		$this->component = $component;
	}

	/**
	 * Returns the category service. If the service is not available
	 * null is returned.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  Categories|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getCategories($section = ''): Categories
	{
		$classname = ucfirst(substr($this->component, 4)) . ucfirst($section) . 'Categories';

		if (!class_exists($classname))
		{
			$path = JPATH_SITE . '/components/' . $this->component . '/helpers/category.php';

			if (!is_file($path))
			{
				return null;
			}

			include_once $path;
		}

		return new $classname([]);
	}
}
