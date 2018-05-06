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
 * Trait for component Service Providers that support Associations built to implement AssociationAwareInterface
 *
 * @since  __DEPLOY_VERSION__
 */
trait AssociationAwareTrait
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
	 * Returns the associations helper.
	 *
	 * @return  AssociationExtensionInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws  Exception\AssociationsNotImplementedException
	 */
	public function getAssociationsExtension(): AssociationExtensionInterface
	{
		if ($this->associationExtension === null)
		{
			throw new Exception\AssociationsNotImplementedException;
		}

		return $this->associationExtension;
	}

	/**
	 * The association extension.
	 *
	 * @param   AssociationExtensionInterface  $associationExtension  The extension
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setAssociationExtension(AssociationExtensionInterface $associationExtension)
	{
		$this->associationExtension = $associationExtension;
	}

}
