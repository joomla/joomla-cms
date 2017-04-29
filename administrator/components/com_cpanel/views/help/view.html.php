<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Admin component
 *
 * @since  1.6
 */
class CpanelViewHelp extends JViewLegacy
{
	private static $notEmpty = false;
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$user     = JFactory::getUser();

		$links = [
			// System configuration
			'help' => [
				'link'    => 'index.php?option=com_admin&view=help',
				'title'   => 'MOD_MENU_HELP_JOOMLA',
				'label'   => 'MOD_MENU_HELP_JOOMLA',
				'desc'    => 'MOD_MENU_HELP_JOOMLA',
				'icon'    => 'info'
			],
			'help_official_forum' => [
				'link'    => 'https://forum.joomla.org',
				'title'   => 'MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM',
				'label'   => 'MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM',
				'desc'    => 'MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM',
				'icon'    => 'info'
			],
			'help_forum' => [
				'link'    => '#',
				'title'   => 'MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM',
				'label'   => 'MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM',
				'desc'    => 'MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM',
				'icon'    => 'info'
			],
			'help_official_language' => [
				'link'    => '#',
				'title'   => 'MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM',
				'label'   => 'MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM',
				'desc'    => 'MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM',
				'icon'    => 'info'
			],
			'help_official_documentation' => [
				'link'    => 'https://docs.joomla.org',
				'title'   => 'MOD_MENU_HELP_DOCUMENTATION',
				'label'   => 'MOD_MENU_HELP_DOCUMENTATION',
				'desc'    => 'MOD_MENU_HELP_DOCUMENTATION',
				'icon'    => 'info'
			],
			'help_official_extensions' => [
				'link'    => 'https://extensions.joomla.org',
				'title'   => 'MOD_MENU_HELP_EXTENSIONS',
				'label'   => 'MOD_MENU_HELP_EXTENSIONS',
				'desc'    => 'MOD_MENU_HELP_EXTENSIONS',
				'icon'    => 'info'
			],
			'help_official_translations' => [
				'link'    => 'https://community.joomla.org/translations.html',
				'title'   => 'MOD_MENU_HELP_TRANSLATIONS',
				'label'   => 'MOD_MENU_HELP_TRANSLATIONS',
				'desc'    => 'MOD_MENU_HELP_TRANSLATIONS',
				'icon'    => 'info'
			],
			'help_resources' => [
				'link'    => 'https://resources.joomla.org',
				'title'   => 'MOD_MENU_HELP_RESOURCES',
				'label'   => 'MOD_MENU_HELP_RESOURCES',
				'desc'    => 'MOD_MENU_HELP_RESOURCES',
				'icon'    => 'info'
			],
			'help_community' => [
				'link'    => 'https://community.joomla.org',
				'title'   => 'MOD_MENU_HELP_COMMUNITY',
				'label'   => 'MOD_MENU_HELP_COMMUNITY',
				'desc'    => 'MOD_MENU_HELP_COMMUNITY',
				'icon'    => 'info'
			],
			'help_security' => [
				'link'    => 'https://developer.joomla.org/security-centre.html',
				'title'   => 'MOD_MENU_HELP_SECURITY',
				'label'   => 'MOD_MENU_HELP_SECURITY',
				'desc'    => 'MOD_MENU_HELP_SECURITY',
				'icon'    => 'info'
			],
			'help_developer' => [
				'link'    => 'https://developer.joomla.org',
				'title'   => 'MOD_MENU_HELP_DEVELOPER',
				'label'   => 'MOD_MENU_HELP_DEVELOPER',
				'desc'    => 'MOD_MENU_HELP_DEVELOPER',
				'icon'    => 'info'
			],
			'help_exchange' => [
				'link'    => 'https://joomla.stackexchange.com',
				'title'   => 'MOD_MENU_HELP_XCHANGE',
				'label'   => 'MOD_MENU_HELP_XCHANGE',
				'desc'    => 'MOD_MENU_HELP_XCHANGE',
				'icon'    => 'info'
			],
			'help_shop' => [
				'link'    => 'https://community.joomla.org/the-joomla-shop.html',
				'title'   => 'MOD_MENU_HELP_SHOP',
				'label'   => 'MOD_MENU_HELP_SHOP',
				'desc'    => 'MOD_MENU_HELP_SHOP',
				'icon'    => 'info'
			],
		];

		if ($user->authorise('core.manage', 'com_admin'))
		{
			static::$notEmpty  = true;
		}

		if (static::$notEmpty)
		{
			$this->links = $links;
		}
		else
		{
			throw new JUserAuthorizationexception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$lang = JFactory::getLanguage();
		$extension = 'mod_menu';
		$base_dir = JPATH_ADMINISTRATOR;
		$language_tag = JFactory::getLanguage()->getTag();
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('Help'), 'info help_header');
	}
}
