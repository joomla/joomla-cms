<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Media component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
 */
class MediaViewMedialistHtml extends ConfigViewCmsHtml
{
	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.5
	 * @throws  RuntimeException
	 */
	public function render()
	{
		$app = JFactory::getApplication();

		if (!$app->isAdmin())
		{
			return $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
		}

		// Do not allow cache
		$app->allowCache(false);

		JHtml::_('behavior.framework', true);

		JFactory::getDocument()->addScriptDeclaration("
		jQuery(function($)
		{
			window.parent.document.updateUploader();
			$('a.img-preview').each(function()
			{
				$(this).on('click', function()
				{
					window.top.document.preview.fromElement(this);
					return false;
				});
			});
		});");

		$images = $this->model->getImages();
		$documents = $this->model->getDocuments();
		$folders = $this->model->getFolders();
		$state = $this->model->getState();

		// Check for invalid folder name
		if ($state->get('folder') == null)
		{
			$dirname = JRequest::getVar('folder', '', '', 'string');

			if (!empty($dirname))
			{
				$dirname = htmlspecialchars($dirname, ENT_COMPAT, 'UTF-8');
				$app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_BROWSE_FOLDER_WARNDIRNAME', $dirname), 'error');

				return;
			}
		}

		$this->baseURL = JUri::root();
		$this->images = &$images;
		$this->documents = &$documents;
		$this->folders = &$folders;
		$this->state = &$state;

		return parent::render();
	}

	/**
	 * Set the folder
	 *
	 * @param   int  $index  Index of the image
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function setFolder($index = 0)
	{
		if (isset($this->folders[$index]))
		{
			$this->_tmp_folder = &$this->folders[$index];
		}
		else
		{
			$this->_tmp_folder = new JObject;
		}
	}

	/**
	 * Set the image
	 *
	 * @param   int  $index  Index of the image
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function setImage($index = 0)
	{
		if (isset($this->images[$index]))
		{
			$this->_tmp_img = &$this->images[$index];
		}
		else
		{
			$this->_tmp_img = new JObject;
		}
	}

	/**
	 * Set the doc
	 *
	 * @param   int  $index  Index of the doc
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function setDoc($index = 0)
	{
		if (isset($this->documents[$index]))
		{
			$this->_tmp_doc = &$this->documents[$index];
		}
		else
		{
			$this->_tmp_doc = new JObject;
		}
	}
}
