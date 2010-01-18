<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Cpanel
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.application.module.helper');

/**
 * HTML View class for the Cpanel component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Cpanel
 * @since 1.0
 */
class CpanelViewCpanel extends JView
{
	public $modules = null;

	public function display($tpl = null)
	{
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('Control Panel'), 'cpanel.png');
		JToolBarHelper::help('screen.cpanel');

		/*
		 * Set the template - this will display cpanel.php
		 * from the selected admin template.
		 */
		JRequest::setVar('tmpl', 'cpanel');

		// Display the cpanel modules
		$modules	= &JModuleHelper::getModules('cpanel');

		$this->assignRef('modules',			$modules);

		parent::display($tpl);
	}
}