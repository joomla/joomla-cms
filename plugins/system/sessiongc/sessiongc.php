<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.sessiongc
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Garbage collection handler for session related data
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemSessionGc extends CMSPlugin
{
	/**
	 * Runs after the HTTP response has been sent to the client and performs garbage collection tasks
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRespond()
	{
		if ($this->params->get('enable_session_gc', true))
		{
			$probability = $this->params->get('gc_probability', 1);
			$divisor     = $this->params->get('gc_divisor', 100);

			$random = $divisor * lcg_value();

			if ($probability > 0 && $random < $probability)
			{
				JFactory::getSession()->gc();
			}
		}
	}
}
