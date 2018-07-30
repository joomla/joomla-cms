<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\CategoriesServiceInterface;
use Joomla\CMS\Categories\CategoriesServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Component\Banners\Administrator\Service\Html\Banner;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_banners
 *
 * @since  __DEPLOY_VERSION__
 */
class BannersComponent extends MVCComponent implements BootableExtensionInterface, CategoriesServiceInterface
{
	use CategoriesServiceTrait;
	use HTMLRegistryAwareTrait;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function boot(ContainerInterface $container)
	{
		$this->getRegistry()->register('banner', new Banner);
	}

	/**
	 * Returns the table for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getTableNameForSection(string $section = null)
	{
		return '#__banners';
	}

	/**
	 * Returns the state column for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getStateColumnForSection(string $section = null)
	{
		return 'published as state';
	}
}
