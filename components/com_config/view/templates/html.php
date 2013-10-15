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
 * View to edit a template style.
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigViewTemplatesHtml extends ConfigViewHtmlCms
{

	public $item;

	public $form;

	/**
	 * Method to display the view.
	 *
	 * @return  void
	 *
	 */
	public function render()
	{

		$user = JFactory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');

		return parent::render();
	}

}
