<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

use Joomla\Application\AbstractApplication;

defined('JPATH_PLATFORM') or die;

/**
 * Event class for representing the application's `onBeforeExecute` event
 *
 * @since  4.0.0
 */
class BeforeExecuteEvent extends AbstractImmutableEvent
{
	/**
	 * Get the event's application object
	 *
	 * @return  AbstractApplication
	 *
	 * @since  4.0.0
	 */
	public function getApplication(): AbstractApplication
	{
		return $this->getArgument('subject');
	}
}
