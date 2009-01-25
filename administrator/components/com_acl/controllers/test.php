<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessControllerTest extends JController
{
	/**
	 * This method runs the install.php file in the Joomla installation application
	 * for testing purposes only
	 */
	function install()
	{
		$file = JPATH_SITE.DS.'installation'.DS.'sql'.DS.'mysql'.DS.'install.php';
		if (file_exists($file)) {
			try {
				require $file;
			}
			catch (JException $e) {
				JError::raiseWarning(500, $e->getMessage());
				$info = $e->get('info');
				print_r($info);
			}
		}
		else {
			JError::raiseWarning(500, 'Install file does not exist');
		}
	}
}
