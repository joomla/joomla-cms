<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Login
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
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
