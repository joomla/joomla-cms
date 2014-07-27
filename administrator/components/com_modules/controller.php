<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Modules manager master display controller.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ModulesController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean         $cachable       If true, the view output will be cached
	 * @param   array|boolean   $urlparams      An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}
	.
	 *
	 * @return  JController        This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/modules.php';

		// Load the submenu.
		ModulesHelper::addSubmenu($this->input->get('view', 'modules'));

		$view   = $this->input->get('view', 'modules');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		return parent::display();
	}
}
