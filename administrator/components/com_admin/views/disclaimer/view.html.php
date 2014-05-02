<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Admin component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @since		1.6
 */
class AdminViewDisclaimer extends JView
{
	
	function display($tpl = null)
	{       
                JToolBarHelper::title(JText::_('COM_JOKTE_DISCLAIMER_JOOMLA_TITLE'), 'weblinks');
                JToolBarHelper::back();
                parent::display($tpl);
	}
}
