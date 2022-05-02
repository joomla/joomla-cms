<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session\EventListener;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Session\MetadataManager;
use Joomla\Registry\Registry;
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
	 * Application configuration.
	 *
	 * @var    Registry
	 * @since  4.0.0
	 */
	private $config;

	/**
	 * Constructor.
	 *
	 * @param   MetadataManager  $metadataManager  Session metadata manager.
	 * @param   Registry         $config           Application configuration.
	 *
	 * @since   4.0.0
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
	 * @since   4.0.0
	 */
	public function onAfterSessionStart(SessionEvent $event)
	{
		if ($this->config->get('session_metadata', true) && $event->getSession()->has('user'))
		{
			$this->metadataManager->createOrUpdateRecord($event->getSession(), $event->getSession()->get('user'));
		}
	}
}
