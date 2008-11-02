<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Login
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Login component
 *
 * @static
 * @package		Joomla
 * @subpackage	Login
 * @since 1.0
 */
class LoginViewLogin extends JView
{
	protected $module = null;

	public function display($tpl = null)
	{
		$module = & JModuleHelper::getModule('login');

		$this->assignRef('module',		$module);

		parent::display($tpl);
	}
}
