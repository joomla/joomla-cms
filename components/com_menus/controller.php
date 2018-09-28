<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Menus manager master display controller.
 *
 * @since  3.7.0
 */
class MenusController extends JControllerLegacy
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

		// Menus frontpage Editor Menu proxying:
		if ($this->input->get('view') === 'items' && $this->input->get('layout') === 'modal')
		{
			JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config);
	}
}
