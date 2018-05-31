<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

/**
 * Joomla Platform CMS Module Dispatcher Interface
 *
 * @since  4.0.0
 */
interface ModuleDispatcherInterface extends DispatcherInterface
{
	/**
	 * Returns the module.
	 *
	 * @return  \stdClass
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModule();

	/**
	 * Sets the module.
	 *
	 * @param   \stdClass  $module  The module
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setModule(\stdClass $module);
}
