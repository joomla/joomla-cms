<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;

/**
 * Event object for retrieving pluggable quick icons
 *
 * @since  __DEPLOY_VERSION__
 */
class GetQuickIconsEvent extends AbstractEvent
{
	/**
	 * The event context
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $context;

	/**
	 * Get the event context
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function setContext($context)
	{
		$this->context = $context;

		return $context;
	}
}
