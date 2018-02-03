<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Quickicon\Administrator\Event;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;

/**
 * Event object for retrieving pluggable quick icons
 *
 * @since  4.0.0
 */
class QuickIconsEvent extends AbstractEvent
{
	/**
	 * The event context
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	private $context;

	/**
	 * Get the event context
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Set the event context
	 *
	 * @param   string  $context  The event context
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function setContext($context)
	{
		$this->context = $context;

		return $context;
	}
}
