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
 * Interface to be implemented by classes depending on a MVC factory.
 *
 * @since  4.0.0
 */
interface MVCFactoryServiceInterface
{
	/**
	 * Creates an MVCFactory for the given application.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  MVCFactoryInterface
	 *
	 * @since  4.0.0
	 */
	public function createMVCFactory(CMSApplicationInterface $application): MVCFactoryInterface;
}
