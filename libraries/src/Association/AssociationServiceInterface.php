<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Association;

defined('JPATH_PLATFORM') or die;

/**
 * The association service.
 *
 * @since  __DEPLOY_VERSION__
 */
interface AssociationServiceInterface
{
	/**
	 * Returns the associations extension helper class.
	 *
	 * @return  AssociationExtensionInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAssociationsExtension(): AssociationExtensionInterface;
}
