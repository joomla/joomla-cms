<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Association
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Association Extension Interface for the helper classes
 *
 * @since  __DEPLOY_VERSION__
 */
interface JAssociationExtensionInterface
{
	/**
	 * Checks if the extension supports associations
	 *
	 * @return  boolean  Supports the extension associations
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasAssociationsSupport();
}
