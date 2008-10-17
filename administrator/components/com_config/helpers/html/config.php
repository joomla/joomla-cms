<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML helper methods for com_config
 *
 * @package		Joomla
 * @subpackage	Config
 */
class JHTMLConfig
{
	function warnicon()
	{
		$tip = '<img src="'.JURI::root().'media/system/images/warning.png" border="0"  alt="" />';
		return $tip;
	}

	/**
	 * Display a list of PHP error reporting options
	 *
	 * @param	int		$selected The selected value
	 * @return	string
	 */
	public function errorReporting($selected = -1)
	{
		$errors = array (
			JHTML::_('select.option',	-1,								JText::_('Config Error System Default')),
			JHTML::_('select.option',	0,								JText::_('Config Error None')),
			JHTML::_('select.option',	E_ERROR | E_WARNING | E_PARSE,	JText::_('Config Error Simple')),
			JHTML::_('select.option',	E_ALL,							JText::_('Config Error Maximum')),
			JHTML::_('select.option',	E_ALL | E_STRICT,				JText::_('Config Error Strict'))
		);
		return JHTML::_('select.genericlist',  $errors, 'error_reporting', 'class="inputbox" size="1"', 'value', 'text', $selected);
	}
}
