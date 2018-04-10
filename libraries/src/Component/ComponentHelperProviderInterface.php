<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component;

defined('JPATH_PLATFORM') or die;

/**
 * Access to component specific helper services.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ComponentHelperProviderInterface
{
	/**
	 * Returns the helper.
	 *
	 * @return  ComponentHelperInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHelper(): ComponentHelperInterface;
}
