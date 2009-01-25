<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Admin
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the Admin component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @since 1.5
 */
class AdminViewChangelog extends JView
{
	function display($tpl = null)
	{
		$this->assign('changelog', $this->get('Changelog'));
		parent::display($tpl);
	}
}