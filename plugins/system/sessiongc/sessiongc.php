<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.sessiongc
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\MetadataManager;

/**
 * Garbage collection handler for session related data
 *
 * @since  3.8.6
 */
class PlgSystemSessionGc extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  3.8.6
	 */
	protected $app;

	/**
	 * Database driver
	 *
	 * @var    JDatabaseDriver
	 * @since  3.8.6
	 */
	protected $db;

	/**
	 * Runs after the HTTP response has been sent to the client and performs garbage collection tasks
	 *
	 * @return  void
	 *
	 * @since   3.8.6
	 */
	public function onAfterRespond()
	{
		$session = Factory::getSession();

		if ($this->params->get('enable_session_gc', 1))
		{
			$probability = $this->params->get('gc_probability', 1);
			$divisor     = $this->params->get('gc_divisor', 100);

			$random = $divisor * lcg_value();

			if ($probability > 0 && $random < $probability)
			{
				$session->gc();
			}
		}

		if ($this->app->get('session_handler', 'none') !== 'database' && $this->params->get('enable_session_metadata_gc', 1))
		{
			$probability = $this->params->get('gc_probability', 1);
			$divisor     = $this->params->get('gc_divisor', 100);

			$random = $divisor * lcg_value();

			if ($probability > 0 && $random < $probability)
			{
				$metadataManager = new MetadataManager($this->app, $this->db);
				$metadataManager->deletePriorTo(time() - $session->getExpire());
			}
		}
	}
}
