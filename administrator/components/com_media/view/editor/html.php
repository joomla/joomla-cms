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
 * HTML View class for the Image editor
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaViewEditorHtml extends JViewHtml
{
	public function render($tpl = null)
	{
		$media = $this->model->getImageInfo();
		$catID = $media->catid;
		$mediaID = $media->content_id;
		$canDo = MediaHelper::getActions($catID, $mediaID);
		if (!$canDo->get('core.edit'))
		{
			$message = JError::__('COM_MEDIA_ERROR_NO_PERMISSION');
			$redirect = 'indexp.php?option=com_media';
			$this->redirect($redirect, $message);
		}


		$app = JFactory::getApplication();
		$config = JComponentHelper::getParams('com_media');

		$lang = JFactory::getLanguage();

		$document = JFactory::getDocument();

		if (DIRECTORY_SEPARATOR == '\\')
		{
			$base = str_replace(DIRECTORY_SEPARATOR, "\\\\", COM_MEDIA_BASE);
		}
		else
		{
			$base = COM_MEDIA_BASE;
		}

		$this->addToolbar();

		return parent::render();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$user = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		$editing = $input->get('editing', '', 'string');
		$path = pathinfo($editing);

		// Set the titlebar text
		JToolbarHelper::title(JText::_('COM_MEDIA'), 'mediamanager.png');

		JHtml::_('jquery.framework');
		JHtml::_('script', 'media/imageeditor.js', true, true);
		JHtml::_('script', 'media/jquery.Jcrop.min.js', true, true);
		JHTML::_('stylesheet', 'media/jquery.Jcrop.min.css', true, true);

		// Add a back button
		if ($user->authorise('core.create', 'com_media'))
		{
			$title = JText::_('COM_MEDIA_BACK');
			$dhtml = "<button onclick=\"window.location = 'index.php?option=com_media&folder=" .
				$path['dirname'] . "'\" class=\"btn btn-small\" id=\"editor_back\">
						<i class=\"\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'folder');
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.admin', 'com_media'))
		{
			JToolbarHelper::preferences('com_media');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_CONTENT_MEDIA_MANAGER');
	}
}
