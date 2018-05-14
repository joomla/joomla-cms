<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.csp
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! CSP Header Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemCsp extends JPlugin
{
	/**
	 * After initialise.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterInitialise()
	{
		$nonce = bin2hex(random_bytes(64));
		JFactory::getApplication()->set('script_nonce', $nonce);
		JFactory::getApplication()->setHeader('Content-Security-Policy', 'default-src \'none\'; style-src \'self\' unsafe-inline https://fonts.googleapis.com/; script-src \'self\' \'nonce-' . $nonce . '\'; font-src \'self\' https://fonts.gstatic.com; img-src \'self\'; connect-src \'self\'; frame-src \'self\'', true);
	}
}
