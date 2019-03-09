<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session\EventListener;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Session\MetadataManager;
use Joomla\Registry\Registry;
use Joomla\Session\SessionEvent;

/**
 * Event listener for session events regarding the session metadata for users.
 *
 * @since  __DEPLOY_VERSION__
 */
final class MetadataManagerListener
{
	/**
	 * Session metadata manager.
	 *
	 * @var    MetadataManager
	 * @since  __DEPLOY_VERSION__
	 */
	private $metadataManager;

	/**
	 * Application configuration.
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	private $config;

	/**
	 * Constructor.
	 *
	 * @param   MetadataManager  $metadataManager  Session metadata manager.
	 * @param   Registry         $config           Application configuration.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(MetadataManager $metadataManager, Registry $config)
	{
		$this->metadataManager = $metadataManager;
		$this->config          = $config;
	}

	/**
	 * Listener for the `session.start` event.
	 *
	 * @param   SessionEvent  $event  The session event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterSessionStart(SessionEvent $event)
	{
		if ($this->config->get('session_metadata', true) && $event->getSession()->has('user'))
		{
			$this->metadataManager->createOrUpdateRecord($event->getSession(), $event->getSession()->get('user'));
		}
	}
}
