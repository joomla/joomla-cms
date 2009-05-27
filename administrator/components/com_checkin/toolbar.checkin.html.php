<?php
/**
 * @version		$Id: toolbar.checkin.html.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 */
class TOOLBAR_checkin {
	/**
	* Draws the menu for a New category
	*/
	function _DEFAULT() {

		JToolBarHelper::title(JText::_('Global Check-in'), 'checkin.png');
		JToolBarHelper::help('screen.checkin');
	}
}