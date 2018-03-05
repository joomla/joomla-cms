<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML;

defined('_JEXEC') or die;

/**
 * Interface to be implemented by classes depending on a HTML registry.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HTMLRegistryAwareInterface
{
	/**
	 * Set the form factory to use.
	 *
	 * @param   Registry $registry  The registry
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRegistry(Registry $registry = null);
}
