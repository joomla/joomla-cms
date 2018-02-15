<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\DI\Container;
use Psr\Container\ContainerInterface;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class ComponentContainer extends Container implements ComponentContainerInterface
{
	/**
	 * Returns the category service. If the service is not available
	 * null is returned.
	 *
	 * @param   string  $section  The section
	 * @param   array   $options  The options
	 *
	 * @return  Categories|null
	 *
	 * @see Categories::setOptions()
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getCategories(array $options = [], $section = '')
	{
		$serviceName = 'categories';

		if ($section)
		{
			$serviceName .= '.' . strtolower($section);
		}

		if (!$this->has($serviceName))
		{
			return null;
		}

		$categories = $this->get($serviceName);
		$categories->setOptions($options);

		return $categories;
	}
}
