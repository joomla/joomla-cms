<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

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
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('joomla', JPATH_ADMINISTRATOR);
		$lang->load('com_modules', JPATH_ADMINISTRATOR);

		return parent::render();
	}
}
