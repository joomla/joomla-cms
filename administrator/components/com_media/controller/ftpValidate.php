<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base ftpValidate Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaControllerFtpValidate extends ConfigControllerDisplay
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');
	}
}
