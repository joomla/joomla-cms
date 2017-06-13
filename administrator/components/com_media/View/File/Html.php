<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\View\File;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\View\HtmlView;

\JLoader::import('joomla.filesystem.file');

/**
 * View to edit an file.
 *
 * @since  __DEPLOY_VERSION__
 */
class Html extends HtmlView
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
		$input = \JFactory::getApplication()->input;

		$this->form = $this->get('Form');

		// The component params
		$this->params = ComponentHelper::getParams('com_media');

		$this->file         = $input->getString('path', null);
		$this->fullFilePath = Uri::root() . $this->params->get('file_path', 'images') . $input->getString('path', null);

		if (!$this->file && \JFile::exists($this->fullFilePath))
		{
			// @todo error handling controller redirect files
			throw new \Exception('Image file does not exist');
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the toolbar buttons
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(\JText::_('COM_MEDIA_EDIT'), 'images mediamanager');

		ToolbarHelper::apply('apply');
		ToolbarHelper::save('save');
		ToolbarHelper::custom('reset', 'refresh', '',  'COM_MEDIA_RESET', false);

		ToolbarHelper::cancel('cancel');
	}
}
