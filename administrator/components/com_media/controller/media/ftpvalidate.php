<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base ftpValidate Controller
 *
 * @since  3.5
 */
class MediaControllerMediaFtpvalidate extends ConfigControllerDisplay
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return null
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		$this->app->redirect(JRoute::_('index.php?option=com_media', false));
	}
}
