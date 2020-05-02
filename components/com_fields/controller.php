<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Base controller class for Fields Component.
 *
 * @since  3.7.0
 */
class FieldsController extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                          'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   3.7.0
	 */
	public function __construct($config = array())
	{
		$this->input = JFactory::getApplication()->input;

		// Frontpage Editor Fields Button proxying:
		if ($this->input->get('view') === 'fields' && $this->input->get('layout') === 'modal')
		{
			// Load the backend language file.
			$lang = JFactory::getLanguage();
			$lang->load('com_fields', JPATH_ADMINISTRATOR);

			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config);
	}
}
