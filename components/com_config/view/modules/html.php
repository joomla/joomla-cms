<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a module.
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigViewModulesHtml extends ConfigViewCmsHtml
{
	public $item;

	public $form;

	public $moduleId;

	/**
	 * Display the view
	 */
	public function render()
	{

		$user = JFactory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');

		$this->moduleId = JFactory::getApplication()->input->get('id');

		return parent::render();
	}
}
