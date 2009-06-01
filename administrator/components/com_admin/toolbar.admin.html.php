<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Admin
 */
class TOOLBAR_admin
{
	function _SYSINFO() {

		JToolBarHelper::title(JText::_('Information'), 'systeminfo.png');
		JToolBarHelper::help('screen.system.info');
	}

	function _CPANEL() {

		JToolBarHelper::title(JText::_('Control Panel'), 'cpanel.png');
		JToolBarHelper::help('screen.cpanel');
	}

	function _HELP() {

		JToolBarHelper::title(JText::_('Help'), 'help_header.png');
	}

	function _PREVIEW() {

		JToolBarHelper::title(JText::_('Preview'));
	}

	function _DEFAULT() {
	}
}