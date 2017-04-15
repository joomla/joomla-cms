<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerViewDefault', dirname(__DIR__) . '/default/view.php');

/**
 * Extension Manager Install View
 *
 * @since  1.5
 */
class InstallerViewInstall extends InstallerViewDefault
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function display($tpl = null)
	{
		$paths        = new stdClass;
		$paths->first = '';

		$this->paths  = &$paths;

		$this->showJedAndWebInstaller = JComponentHelper::getParams('com_installer')->get('show_jed_info', 1);

		JPluginHelper::importPlugin('installer');

		JFactory::getApplication()->triggerEvent('onInstallerBeforeDisplay', array(&$this->showJedAndWebInstaller, $this));

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		parent::addToolbar();
		JToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_INSTALL');
	}
}
