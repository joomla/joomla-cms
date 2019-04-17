<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Displays a list of the installed languages.
 *
 * @since  1.6
 */
class LanguagesViewInstalled extends JViewLegacy
{
	/**
	 * @var object client object.
	 * @deprecated 4.0
	 */
	protected $client = null;

	/**
	 * @var boolean|JException True, if FTP settings should be shown, or an exception.
	 */
	protected $ftp = null;

	/**
	 * @var string option name.
	 */
	protected $option = null;

	/**
	 * @var object pagination information.
	 */
	protected $pagination = null;

	/**
	 * @var array languages information.
	 */
	protected $rows = null;

	/**
	 * @var object user object
	 */
	protected $user = null;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->ftp           = $this->get('Ftp');
		$this->option        = $this->get('Option');
		$this->pagination    = $this->get('Pagination');
		$this->rows          = $this->get('Data');
		$this->total         = $this->get('Total');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		LanguagesHelper::addSubmenu('installed');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

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
		$canDo = JHelperContent::getActions('com_languages');

		if ((int) $this->state->get('client_id') === 1)
		{
			JToolbarHelper::title(JText::_('COM_LANGUAGES_VIEW_INSTALLED_ADMIN_TITLE'), 'comments-2 langmanager');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_LANGUAGES_VIEW_INSTALLED_SITE_TITLE'), 'comments-2 langmanager');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::makeDefault('installed.setDefault');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin'))
		{
			// Add install languages link to the lang installer component.
			$bar = JToolbar::getInstance('toolbar');

			// Switch administrator language
			if ($this->state->get('client_id', 0) == 1)
			{
				JToolbarHelper::custom('installed.switchadminlanguage', 'refresh', 'refresh', 'COM_LANGUAGES_SWITCH_ADMIN', false);
				JToolbarHelper::divider();
			}

			$bar->appendButton('Link', 'upload', 'COM_LANGUAGES_INSTALL', 'index.php?option=com_installer&view=languages');
			JToolbarHelper::divider();

			JToolbarHelper::preferences('com_languages');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_INSTALLED');

		$this->sidebar = JHtmlSidebar::render();
	}
}
