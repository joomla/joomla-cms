<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Association;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\CategoriesServiceInterface;

/**
 * Trait to implement AssociationServiceInterface
 *
 * @since  4.0.0
 */
trait AssociationServiceTrait
{
	/**
	 * The association extension.
	 *
	 * @var AssociationExtensionInterface
	 *
	 * @since  4.0.0
	 */
	private $associationExtension = null;

	/**
	 * Returns the associations extension helper class.
	 *
	 * @return  AssociationExtensionInterface
	 *
	 * @since  4.0.0
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
	 * @since  4.0.0
	 */
	public function setAssociationExtension(AssociationExtensionInterface $associationExtension)
	{
		$this->associationExtension = $associationExtension;
	}

	/**
	 * Are categories associations supported.
	 *
	 * @return  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function hasAssociationsCategorySupport()
	{
		return $this instanceof CategoriesServiceInterface;
	}
}
