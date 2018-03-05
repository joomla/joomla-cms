<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;

/**
 * Dispatcher factory interface
 *
 * @since  __DEPLOY_VERSION__
 */
interface DispatcherFactoryInterface
{
	/**
	 * Creates a dispatcher.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createDispatcher(CMSApplicationInterface $application): DispatcherInterface;
}
