<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

include_once __DIR__ . '/../default/view.php';

/**
 * Extension Manager Install View
 *
 * @since  1.5
 */
class InstallerViewInstall extends InstallerViewDefault
{
	/**
	 * @var  stdClass
	 */
	protected $paths;

	/**
	 * @var  bool
	 *
	 * @deprecated   Use $this->state->get('install.show_jed_info');
	 */
	protected $showJedAndWebInstaller;

	/**
	 * @var  stdClass[]
	 */
	protected $installTypes;

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
		$paths = new stdClass;
		$paths->first = '';

		$this->paths        = $paths;
		$this->state        = $this->get('state');
		$this->installTypes = $this->get('InstallTypes');

		if ($errors = $this->get('Errors'))
		{
			JLog::add(implode('<br>', $errors), JLog::WARNING, 'jerror');

			return;
		}

		if (count($this->installTypes) == 0)
		{
			JLog::add(JText::_('COM_INSTALLER_NO_INSTALLATION_PLUGINS_FOUND'), JLog::WARNING, 'jerror');
		}

		$show = $this->state->get('install.show_jed_info');

		JPluginHelper::importPlugin('installer');

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onInstallerBeforeDisplay', array(&$show, $this));

		// Sync both the deprecated and new value
		$this->showJedAndWebInstaller = $show;
		$this->state->set('install.show_jed_info', $show);

		parent::display($tpl);

		$dispatcher->trigger('onInstallerAfterDisplay');
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
