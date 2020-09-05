<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Association;

\defined('JPATH_PLATFORM') or die;

/**
 * The association service.
 *
 * @since  4.0.0
 */
interface AssociationServiceInterface
{
	/**
	 * Returns the associations extension helper class.
	 *
	 * @return  AssociationExtensionInterface
	 *
	 * @since  4.0.0
	 */
	public function getAssociationsExtension(): AssociationExtensionInterface;
}
