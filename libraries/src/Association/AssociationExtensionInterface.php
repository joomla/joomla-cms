<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Association;

defined('JPATH_PLATFORM') or die;

/**
 * Association Extension Interface for the helper classes
 *
 * @since  3.7.0
 */
interface AssociationExtensionInterface
{
	/**
	 * Checks if the extension supports associations
	 *
	 * @return  boolean  Supports the extension associations
	 *
	 * @since   3.7.0
	 */
	public function hasAssociationsSupport();
}
