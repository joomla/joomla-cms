<?php
/**
 * @version		$Id: view.html.php 11476 2009-01-25 06:58:51Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Login component
 * 
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @since		1.6
 */
class LoginViewLogin extends JView
{
	protected $_module = null;

	/**
	 * Execute and display a template script.
	 * 
	 * @param 	string	$tpl	The name of the template file to parse;
	 * @return	void
	 */
	public function display($tpl = null)
	{
		$_module = &JModuleHelper::getModule('login');

		$this->assignRef('module', $_module);

		parent::display($tpl);
	}
}