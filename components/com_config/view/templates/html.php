<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a template style.
 *
 * @since  3.2
 */
class ConfigViewTemplatesHtml extends ConfigViewCmsHtml
{
	public $item;

	public $form;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.2
	 */
	public function render()
	{
		$user = JFactory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');

		return parent::render();
	}
}
