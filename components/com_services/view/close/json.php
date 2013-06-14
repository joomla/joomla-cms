<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once dirname(dirname(__DIR__)) . '/helper/component.php';

/**
 * View for the component configuration
 *
 * @package     Joomla.Site
 * @subpackage  com_services
 * @since       3.2
 */
class ConfigViewCloseJson extends JViewLegacy
{
	/**
	 * Display the view
	 * 
	 * @param   string  $tpl  Layout
	 * 
	 * @return  string
	 * 
	 * @since 3.1
	 */
	public function render($tpl = null)
	{

		echo json_encode('To Be Implemented !');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * 
	 * @since   3.1
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_($this->component->option . '_configuration'), 'config.png');
		JToolbarHelper::apply('component.apply');
		JToolbarHelper::save('component.save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('component.cancel');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
