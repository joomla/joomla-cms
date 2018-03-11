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

/**
 * Factory to create MVC factories.
 *
 * @since  __DEPLOY_VERSION__
 */
interface MVCFactoryFactoryInterface
{
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
	public function createFactory(CMSApplicationInterface $application): MVCFactoryInterface;
}
