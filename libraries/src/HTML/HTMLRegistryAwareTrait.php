<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML;

defined('_JEXEC') or die;

/**
 * Defines the trait for a HTML Registry aware class.
 *
 * @since  4.0.0
 */
trait HTMLRegistryAwareTrait
{
	/**
	 * The registry
	 *
	 * @var    HTMLRegistry
	 * @since  4.0.0
	 */
	private $htmlRegistry;

	/**
	 * Get the registry.
	 *
	 * @return  HTMLRegistry
	 *
	 * @since   4.0.0
	 * @throws  \UnexpectedValueException May be thrown if the registry has not been set.
	 */
	public function getHTMLRegistry()
	{
		if ($this->htmlRegistry)
		{
			return $this->htmlRegistry;
		}

		throw new \UnexpectedValueException('HTML registry not set in ' . __CLASS__);
	}

	/**
	 * Set the registry to use.
	 *
	 * @param   HTMLRegistry  $htmlRegistry  The registry
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setHTMLRegistry(HTMLRegistry $htmlRegistry = null)
	{
		$this->htmlRegistry = $htmlRegistry;
	}
}
