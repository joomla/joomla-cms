<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Joomla! update notification plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Joomla
 * @since       __DEPLOY_VERSION__
 */
class PlgQuickiconEosnotify extends JPlugin
{
	/**
	 * The Application object
	 *
	 * @var    JApplicationSite
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * This method is called when the Quick Icons module is constructing its set
	 * of icons. You can return an array which defines a single icon and it will
	 * be rendered right after the stock Quick Icons.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return array A list of icon definition associative arrays, consisting of the
	 *				 keys link, image, text and access.
	 *
	 * @since       __DEPLOY_VERSION__
	 */
	public function onGetIcons($context)
	{
		if (!$this->app->isClient('administrator') || version_compare(JVERSION, '3.10', '>=') || Factory::getDate()->toSql() <= '2021-04-15')
		{
			return array();
		}

		if ($this->app->input->get('option') == 'com_cpanel')
		{
			$this->app->enqueueMessage(Text::sprintf('PLG_QUICKICON_EOSNOTIFY_CLICK_FOR_INFORMATION_MESSAGE', 'href="https://www.joomla.org/" target="_blank"'), 'warning');

		}

		return array(array(
			'link' => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
			'image' => 'info-circle',
			'text' => '<span class="alert-error">' . Text::_(PLG_QUICKICON_EOSNOTIFY_CLICK_FOR_INFORMATION_WITH_LINK_QUICKLINK) . '</span>',
			'id' => 'plg_quickicon_eos',
			'group' => Text::_(PLG_QUICKICON_EOSNOTIFY_GROUP)
		));
	}
}
