<?php
/**
 * @version		$Id: toolbar.config.html.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class TOOLBAR_config
{
	function _DEFAULT() {

		JToolBarHelper::title(JText::_('Global Configuration'), 'config.png');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel('cancel', 'Close');
		JToolBarHelper::help('screen.config');
	}
}