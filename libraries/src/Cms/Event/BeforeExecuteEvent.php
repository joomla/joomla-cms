<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Event;

use Joomla\Application\AbstractApplication;

defined('JPATH_PLATFORM') or die;

/**
 * Event class for representing the application's `onBeforeExecute` event
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeExecuteEvent extends AbstractImmutableEvent
{
	/**
	 * Get the event's application object
	 *
	 * @return  AbstractApplication
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getApplication()
	{
		return $this->getArgument('subject');
	}
}
