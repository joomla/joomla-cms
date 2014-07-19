<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Editor component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
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

		$ftp = !JClientHelper::hasCredentials('ftp');

		$session	= JFactory::getSession();
		$state		= $this->model->getState();

		$this->id		= $app->input->get('id');
		$this->item		= $this->model->getItem($this->id);
		$this->form		= $this->model->getForm();

		$this->session = $session;
		$this->config = &$config;
		$this->state = &$state;
		$this->require_ftp = $ftp;

		// From template manager
		$this->folder   = $app->input->get('folder', '', 'path');
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
	 * @return void
	 *
	 * @since   3.5
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

			// Actions on media
			JToolbarHelper::custom('media.crop.editor', 'move', 'move', 'COM_MEDIA_EDITOR_BUTTON_CROP', false);
			JToolbarHelper::modal('resizeModal', 'icon-contract-2', 'COM_MEDIA_EDITOR_BUTTON_RESIZE');
			JToolbarHelper::modal('rotateModal', 'icon-loop', 'COM_MEDIA_EDITOR_BUTTON_ROTATE');
			JToolbarHelper::modal('filterModal', 'icon-filter', 'COM_MEDIA_EDITOR_BUTTON_FILTER');
			JToolbarHelper::modal('thumbsModal', 'icon-grid', 'COM_MEDIA_EDITOR_BUTTON_THUMBS');

			JToolbarHelper::divider();
		}

			JToolbarHelper::cancel('media.cancel.editor');

			JToolbarHelper::divider();
			JToolbarHelper::help('JHELP_CONTENT_MEDIA_MANAGER_EDITOR');
	}

}
