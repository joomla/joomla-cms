<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Admin component
 *
 * @since  1.6
 */
class AdminViewHelp extends JViewLegacy
{
	/**
	 * The search string
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $help_search = null;

	/**
	 * The page to be viewed
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $page = null;

	/**
	 * The iso language tag
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $lang_tag = null;

	/**
	 * Table of contents
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $toc = array();

	/**
	 * URL for the latest version check
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $latest_version_check = 'https://www.joomla.org/download.html';

	/**
	 * URL for the start here link
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $start_here = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->help_search          = $this->get('HelpSearch');
		$this->page                 = $this->get('Page');
		$this->toc                  = $this->get('Toc');
		$this->lang_tag             = $this->get('LangTag');
		$this->latest_version_check = $this->get('LatestVersionCheck');

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_ADMIN_HELP'), 'support help_header');
	}
}
