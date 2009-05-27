<?php
/**
 * @version		$Id: toolbar.cpanel.html.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Admin
 */
class TOOLBAR_cpanel
{
	function _DEFAULT() {
		JToolBarHelper::title(JText::_('Control Panel'), 'cpanel.png');
		JToolBarHelper::help('screen.cpanel');
	}
}