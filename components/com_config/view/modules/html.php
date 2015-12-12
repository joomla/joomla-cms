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

	/**
	 * Display the view
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.2
	 */
	public function render()
	{
		$lang = JFactory::getApplication()->getLanguage();
		$lang->load('', JPATH_ADMINISTRATOR, $lang->getTag());
		$lang->load('com_modules', JPATH_ADMINISTRATOR, $lang->getTag());

		return parent::render();
	}
}
