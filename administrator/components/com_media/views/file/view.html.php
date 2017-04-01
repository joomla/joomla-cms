<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

/**
 * View to edit an file.
 *
 * @todo Prototype!
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaViewFile extends JViewLegacy
{

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;

		$this->form = $this->get('Form');

		// The component params
		$this->params = JComponentHelper::getParams('com_media');

		$this->file         = $input->getString('path', null);
		$this->fullFilePath = JUri::root() . $this->params->get('file_path', 'images') . '/' . $input->getString('path', null);

		if (!$this->file && JFile::exists($this->fullFilePath))
		{
			// @todo error handling controller redirect files
			throw new Exception('Image file does not exist');
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the toolbar buttons
	 *
	 * @return  void
	 *
	 * @since   _DEPLOY_VERSION
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_MEDIA_EDIT'), 'images mediamanager');

		// @todo buttons
		JToolbarHelper::apply('file.apply');
		JToolbarHelper::save('file.save');
		JToolbarHelper::cancel('file.cancel');
	}
}
