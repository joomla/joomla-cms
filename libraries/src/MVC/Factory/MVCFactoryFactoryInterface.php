<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Factory;

use Joomla\CMS\Application\CMSApplicationInterface;

defined('JPATH_PLATFORM') or die;

/**
 * Factory to create MVC factories.
 *
 * @since  __DEPLOY_VERSION__
 */
interface MVCFactoryFactoryInterface
{
	/**
	 * Method to load and return a factory object.
	 *
	 * @param   string                   $extensionName  The name of the extension, eg. com_content.
	 * @param   CMSApplicationInterface  $app            The application.
	 *
	 * @return  \Joomla\CMS\MVC\Factory\MVCFactoryInterface  The factory object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function createFactory($extensionName, CMSApplicationInterface $app);
}
