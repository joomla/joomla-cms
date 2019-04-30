<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session\EventListener;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Session\MetadataManager;
use Joomla\Session\SessionEvent;

/**
 * Event listener for session events regarding the session metadata for users.
 *
 * @since  4.0.0
 */
final class MetadataManagerListener
{
	/**
	 * Session metadata manager.
	 *
	 * @var    MetadataManager
	 * @since  4.0.0
	 */
	private $metadataManager;

	/**
	 * Constructor.
	 *
	 * @param   MetadataManager  $metadataManager  Session metadata manager.
	 *
	 * @since   4.0.0
	 */
	public function __construct(MetadataManager $metadataManager)
	{
		$this->metadataManager = $metadataManager;
	}

	/**
	 * Listener for the `session.start` event.
	 *
	 * @param   SessionEvent  $event  The session event.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onAfterSessionStart(SessionEvent $event)
	{
		if ($event->getSession()->has('user'))
		{
			$this->metadataManager->createOrUpdateRecord($event->getSession(), $event->getSession()->get('user'));
		}
	}
}
