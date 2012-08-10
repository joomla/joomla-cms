<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Cpanel component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 * @since       1.0
 */
class CpanelViewCpanel extends JViewLegacy
{
	protected $modules = null;

	public function display($tpl = null)
	{
		// Set toolbar items for the page
		JToolbarHelper::title(JText::_('COM_CPANEL'), 'cpanel.png');
		JToolbarHelper::help('screen.cpanel');

		/*
		 * Set the template - this will display cpanel.php
		 * from the selected admin template.
		 */
		JRequest::setVar('tmpl', 'cpanel');

		// Display the cpanel modules
		$this->modules = JModuleHelper::getModules('cpanel');

		parent::display($tpl);
	}
}
