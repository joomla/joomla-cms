<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Massmail
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Massmail
 */
class TOOLBAR_massmail
{
	/**
	* Draws the menu for a New Contact
	*/
	function _DEFAULT() {

		JToolBarHelper::title(JText::_('Mass_Mail_Users'), 'massmail.png');
		JToolBarHelper::custom('send','send.png','send_f2.png','Send Mail',false);
		JToolBarHelper::cancel();
		JToolBarHelper::preferences('com_massmail');
		JToolBarHelper::help('screen.users.massmail');
	}
}