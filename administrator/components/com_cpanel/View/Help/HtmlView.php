<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Cpanel\Administrator\View\Help;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for the Cpanel component
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var  boolean  $notEmpty  Check if there are links to be displayed
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private static $notEmpty;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('Help'), 'info help_header');
		ToolbarHelper::help('screen.cpanel');

		$user  = Factory::getUser();
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
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		Factory::getLanguage()->load(
			'mod_menu',
			JPATH_ADMINISTRATOR,
			Factory::getLanguage()->getTag(),
			true
		);

		return parent::display($tpl);
	}
}
