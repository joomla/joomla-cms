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
 * Defines the trait for a HTML Registry aware class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait HTMLRegistryAwareTrait
{
	/**
	 * The registry
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	private $registry;

	/**
	 * Get the registry.
	 *
	 * @return  Registry
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException May be thrown if the registry has not been set.
	 */
	public function getRegistry()
	{
		if ($this->registry)
		{
			return $this->registry;
		}

		throw new \UnexpectedValueException('HTML registry not set in ' . __CLASS__);
	}

	/**
	 * Set the registry to use.
	 *
	 * @param   Registry  $registry  The registry
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRegistry(Registry $registry = null)
	{
		$this->registry = $registry;
	}
}
