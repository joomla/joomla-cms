<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;

/**
 * Event class for representing the application's `onError` event
 *
 * @since  __DEPLOY_VERSION__
 */
class ErrorEvent extends AbstractEvent
{
	/**
	 * Get the event's application object
	 *
	 * @return  AbstractApplication
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getApplication(): AbstractApplication
	{
		return $this->getArgument('application');
	}

	/**
	 * Get the event's error object
	 *
	 * @return  \Throwable
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getError(): \Throwable
	{
		return $this->getArgument('subject');
	}

	/**
	 * Set the event's error object
	 *
	 * @param   \Throwable  $error  The new error to process
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setError(\Throwable $error)
	{
		$this->setArgument('subject', $error);
	}
}
