<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Media component
 *
 * @since  1.0
 */
class MediaViewMediaList extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		if (!$app->isAdmin())
		{
			return $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
		}

		// Do not allow cache
		$app->allowCache(false);

		$images    = $this->get('images');
		$documents = $this->get('documents');
		$folders   = $this->get('folders');
		$videos    = $this->get('videos');
		$state     = $this->get('state');

		// Check for invalid folder name
		if (empty($state->folder))
		{
			$dirname = JFactory::getApplication()->input->getPath('folder', '');

			if (!empty($dirname))
			{
				$dirname = htmlspecialchars($dirname, ENT_COMPAT, 'UTF-8');
				JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_BROWSE_FOLDER_WARNDIRNAME', $dirname));
			}
		}

		$this->baseURL   = JUri::root();
		$this->images    = &$images;
		$this->documents = &$documents;
		$this->folders   = &$folders;
		$this->state     = &$state;
		$this->videos    = &$videos;

		parent::display($tpl);
	}

	/**
	 * Set the active folder
	 *
	 * @param   integer  $index  Folder position
	 *
	 * @return  void
	 *
	 * @since   1.0
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
	 * Set the active image
	 *
	 * @param   integer  $index  Image position
	 *
	 * @return  void
	 *
	 * @since   1.0
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
	 * Set the active doc
	 *
	 * @param   integer  $index  Doc position
	 *
	 * @return  void
	 *
	 * @since   1.0
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

	/**
	 * Set the active video
	 *
	 * @param   integer  $index  Doc position
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function setVideo($index = 0)
	{
		if (isset($this->videos[$index]))
		{
			$this->_tmp_video = &$this->videos[$index];
		}
		else
		{
			$this->_tmp_video = new JObject;
		}
	}
}
