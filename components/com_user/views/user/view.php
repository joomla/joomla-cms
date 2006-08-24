<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class UserViewUser extends JView
{
	function __construct()
	{
		$this->setViewName('user');
		$this->setTemplatePath(dirname(__FILE__).DS.'tmpl');
	}

	function display()
	{
		switch($this->request->task)
		{
			case 'edit' :
				$this->_displayEdit();
				break;
			default     :
				$this->_displayUser();
		}
	}

	function _displayUser()
	{
		$this->_loadTemplate('user');
	}

	function _displayEdit()
	{
		global $mainframe;

		require_once( JPATH_SITE .'/includes/HTML_toolbar.php' );

		mosCommonHTML::loadOverlib();

		// check to see if Frontend User Params have been enabled
		$check = $mainframe->getCfg('frontend_userparams');
		if ($check == '1' || $check == 1 || $check == NULL) {
			$params = $this->user->getParameters();
			$params->loadSetupFile(JApplicationHelper::getPath( 'com_xml', 'com_users' ));
		}

		$this->_loadTemplate('edit');
	}
}
?>