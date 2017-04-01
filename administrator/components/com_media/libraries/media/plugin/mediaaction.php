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
 * Media Manager Base Plugin for the media actions
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaActionPlugin extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		// Check if it is the right form
		if ($form->getName() != 'com_media.file')
		{
			return;
		}

		// Get the layout file to add the JS code
		$layout = JPluginHelper::getLayoutPath('media-action', $this->_name, $this->_name);

		// If there is a layout, include it
		if ($layout)
		{
			include $layout;
		}

		// The file with the params for the edit view
		$paramsFile = JPATH_PLUGINS . '/media-action/' . $this->_name . '/form/' . $this->_name . '.xml';

		// When the file exists, load it into the form
		if (file_exists($paramsFile))
		{
			$form->loadFile($paramsFile);
		}
	}
}
