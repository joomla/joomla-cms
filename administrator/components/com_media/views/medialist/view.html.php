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
 * HTML View class for the Media component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.0
 */
class MediaViewMediaList extends JViewLegacy
{
	public function display($tpl = null)
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
		window.addEvent('domready', function()
		{
			window.parent.document.updateUploader();
			$$('a.img-preview').each(function(el)
			{
				el.addEvent('click', function(e)
				{
					window.top.document.preview.fromElement(el);
					return false;
				});
			});
		});");

		$images = $this->get('images');
		$documents = $this->get('documents');
		$folders = $this->get('folders');
		$state = $this->get('state');

		// Check for invalid folder name
		if (empty($state->folder)) {
			$dirname = JRequest::getVar('folder', '', '', 'string');
			if (!empty($dirname)) {
				$dirname = htmlspecialchars($dirname, ENT_COMPAT, 'UTF-8');
				JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_BROWSE_FOLDER_WARNDIRNAME', $dirname));
			}
		}

		$this->baseURL = JUri::root();
		$this->images = &$images;
		$this->documents = &$documents;
		$this->folders = &$folders;
		$this->state = &$state;

		parent::display($tpl);
	}

	function setFolder($index = 0)
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

	function setImage($index = 0)
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

	function setDoc($index = 0)
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
