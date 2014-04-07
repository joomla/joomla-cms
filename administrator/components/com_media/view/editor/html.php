<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Editor component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.0
 */
class MediaViewEditorHtml extends ConfigViewCmsHtml
{
	/**
	 * For loading image information
	 */
	protected $image;
	
	public $item;

	public function render()
	{
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$config = JComponentHelper::getParams('com_media');

		if (!$app->isAdmin())
		{
			return $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
		}

		// Only allow for users wit edit permission
		if (!$user->authorise('core.edit', 'com_media'))
		{
			return;
		}

		$lang	= JFactory::getLanguage();

		$document = JFactory::getDocument();

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		$ftp = !JClientHelper::hasCredentials('ftp');

		$session	= JFactory::getSession();
		$state		= $this->model->getState();
		
		$this->item		= $this->model->getItem();
		$this->form		= $this->model->getForm();
		
		$this->session = $session;
		$this->config = &$config;
		$this->state = &$state;
		$this->require_ftp = $ftp;

		// From template manager
		$this->folder   = $app->input->get('folder');
		$this->file     = $app->input->get('file');
		$explodeArray   = explode('.', $this->file);
		$ext            = end($explodeArray);
		$imageExts      = explode(',', $config->get('image_extensions'));
		
		if (in_array($ext, $imageExts))
		{
			$this->image = $this->model->getImage();
		}
		else
		{
			// File extension not allowed
			return $app->enqueueMessage(JText::_('JERROR'), 'warning');
		}
		
		// Set the toolbar
		$this->addToolbar();

		return parent::render();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$user		= JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_MEDIA_EDITOR'), 'images mediamanager');

		// If not checked out, can save the item.
		if ($user->authorise('core.edit', 'com_media'))
		{
			JToolbarHelper::apply('media.save.editor.apply');
			JToolbarHelper::save('media.save.editor.save');

		
		}

		// If an existing item, can save to a copy.
// 		if ($user->authorise('core.create', 'com_media'))
// 		{
// 			JToolbarHelper::save2copy('media.save.editor.save2copy');
// 		}

		JToolbarHelper::cancel('media.cancel.editor');

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_CONTENT_MEDIA_MANAGER_EDITOR');
	}

}
