<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;

defined('_JEXEC') or die;

/**
 * Joomla Platform CMS Dispatcher Interface
 *
 * @since  4.0.0
 */
interface DispatcherFactoryInterface
{
	/**
	 * Creates a dispatcher.
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createDispatcher(CMSApplicationInterface $application): DispatcherInterface;
}
