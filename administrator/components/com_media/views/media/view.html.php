<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Media component
 *
 * @since  1.0
 */
class MediaViewMedia extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app    = JFactory::getApplication();
		$config = JComponentHelper::getParams('com_media');

		if (!$app->isClient('administrator'))
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

			return;
		}

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		$ftp = !JClientHelper::hasCredentials('ftp');

		$session           = JFactory::getSession();
		$state             = $this->get('state');
		$this->session     = $session;
		$this->config      = &$config;
		$this->state       = &$state;
		$this->require_ftp = $ftp;
		$this->folders_id  = ' id="media-tree"';
		$this->folders     = $this->get('folderTree');

		$this->sidebar = JHtmlSidebar::render();

		// Set the toolbar
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
		// Get the toolbar object instance
		$bar  = JToolbar::getInstance('toolbar');
		$user = JFactory::getUser();

		// Set the titlebar text
		JToolbarHelper::title(JText::_('COM_MEDIA'), 'images mediamanager');

		// Add an upload button
		if ($user->authorise('core.create', 'com_media'))
		{
			// Instantiate a new JLayoutFile instance and render the layout
			$layout = new JLayoutFile('toolbar.uploadmedia');

			$bar->appendButton('Custom', $layout->render(array()), 'upload');
			JToolbarHelper::divider();
		}

		// Add a create folder button
		if ($user->authorise('core.create', 'com_media'))
		{
			// Instantiate a new JLayoutFile instance and render the layout
			$layout = new JLayoutFile('toolbar.newfolder');

			$bar->appendButton('Custom', $layout->render(array()), 'create');
			JToolbarHelper::divider();
		}

		// Add a delete button
		if ($user->authorise('core.delete', 'com_media'))
		{
			// Instantiate a new JLayoutFile instance and render the layout
			$layout = new JLayoutFile('toolbar.deletemedia');

			$bar->appendButton('Custom', $layout->render(array()), 'delete');
			JToolbarHelper::divider();
		}

		// Add a preferences button
		if ($user->authorise('core.admin', 'com_media') || $user->authorise('core.options', 'com_media'))
		{
			JToolbarHelper::preferences('com_media');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_CONTENT_MEDIA_MANAGER');
	}

	/**
	 * Display a folder level
	 *
	 * @param   array  $folder  Array with folder data
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getFolderLevel($folder)
	{
		$this->folders_id = null;
		$txt              = null;

		if (isset($folder['children']) && count($folder['children']))
		{
			$tmp           = $this->folders;
			$this->folders = $folder;
			$txt           = $this->loadTemplate('folders');
			$this->folders = $tmp;
		}

		return $txt;
	}
}
