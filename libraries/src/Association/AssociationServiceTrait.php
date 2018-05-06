<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Association;

defined('JPATH_PLATFORM') or die;

/**
 * Trait to implement AssociationServiceInterface
 *
 * @since  __DEPLOY_VERSION__
 */
trait AssociationServiceTrait
{
	/**
	 * The association extension.
	 *
	 * @var AssociationExtensionInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $associationExtension = null;

	/**
	 * Returns the associations extension helper class.
	 *
	 * @return  AssociationExtensionInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAssociationsExtension(): AssociationExtensionInterface
	{
		return $this->associationExtension;
	}

	/**
	 * The association extension.
	 *
	 * @param   AssociationExtensionInterface  $associationExtension  The extension
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setAssociationExtension(AssociationExtensionInterface $associationExtension)
	{
		$this->associationExtension = $associationExtension;
	}
}
