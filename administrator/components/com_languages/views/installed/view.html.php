<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Displays a list of the installed languages.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesViewInstalled extends JViewLegacy
{
	/**
	 * @var object client object
	 */
	protected $client = null;

	/**
	 * @var boolean|JExeption True, if FTP settings should be shown, or an exeption
	 */
	protected $ftp = null;

	/**
	 * @var string option name
	 */
	protected $option = null;

	/**
	 * @var object pagination information
	 */
	protected $pagination=null;

	/**
	 * @var array languages information
	 */
	protected $rows=null;

	/**
	 * @var object user object
	 */
	protected $user = null;

	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		// Get data from the model
		$this->ftp			= $this->get('Ftp');
		$this->option		= $this->get('Option');
		$this->pagination	= $this->get('Pagination');
		$this->rows			= $this->get('Data');
		$this->state		= $this->get('State');

		$document = JFactory::getDocument();
		$document->setBuffer($this->loadTemplate('navigation'), 'modules', 'submenu');

		$this->addToolbar();
		parent::display($tpl);
	}
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/languages.php';

		$canDo	= LanguagesHelper::getActions();

		JToolBarHelper::title(JText::_('COM_LANGUAGES_VIEW_INSTALLED_TITLE'), 'langmanager.png');

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::makeDefault('installed.setDefault');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			// Add install languages link to the lang installer component
			$bar = JToolBar::getInstance('toolbar');
			$bar->appendButton('Link', 'extension', 'COM_LANGUAGES_INSTALL', 'index.php?option=com_installer&view=languages');
			JToolBarHelper::divider();

			JToolBarHelper::preferences('com_languages');
			JToolBarHelper::divider();
		}

		JToolBarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_INSTALLED');
	}
}
