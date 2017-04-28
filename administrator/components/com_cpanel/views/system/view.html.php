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
class CpanelViewSystem extends JViewLegacy
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
			'com_config' => [
				'link'    => 'index.php?option=com_config',
				'enabled' => false,
				'title'   => 'MOD_MENU_CONFIGURATION',
				'label'   => 'MOD_MENU_CONFIGURATION',
				'desc'    => 'MOD_MENU_CONFIGURATION',
				'icon'    => 'cog'
			],
			'sysinfo' => [
				'link'    => 'index.php?option=com_admin&view=sysinfo',
				'enabled' => false,
				'title'   => 'MOD_MENU_SYSTEM_INFORMATION',
				'label'   => 'MOD_MENU_SYSTEM_INFORMATION',
				'desc'    => 'MOD_MENU_SYSTEM_INFORMATION',
				'icon'    => 'info'
			],
			'com_postinstall' => [
				'link'    => 'index.php?option=com_admin&view=sysinfo',
				'enabled' => false,
				'title'   => 'Post install messages',
				'label'   => 'Post install messages',
				'desc'    => 'Post install messages',
				'icon'    => 'info-circle'
			],

			// Maintenance
			'com_checkin' => [
				'link'    => 'index.php?option=com_checkin',
				'enabled' => false,
				'title'   => 'MOD_MENU_GLOBAL_CHECKIN',
				'label'   => 'MOD_MENU_GLOBAL_CHECKIN',
				'desc'    => 'MOD_MENU_GLOBAL_CHECKIN',
				'icon'    => 'refresh'
			],
			'com_cache' => [
				'link'    => 'index.php?option=com_cache',
				'enabled' => false,
				'title'   => 'MOD_MENU_CLEAR_CACHE',
				'label'   => 'MOD_MENU_CLEAR_CACHE',
				'desc'    => 'MOD_MENU_CLEAR_CACHE',
				'icon'    => 'trash'
			],
			'com_cache_purge' => [
				'link'    => 'index.php?option=com_cache&view=purge',
				'enabled' => false,
				'title'   => 'MOD_MENU_PURGE_EXPIRED_CACHE',
				'label'   => 'MOD_MENU_PURGE_EXPIRED_CACHE',
				'desc'    => 'MOD_MENU_PURGE_EXPIRED_CACHE',
				'icon'    => 'trash'
			],
			'database' => [
				'link'    => 'index.php?option=com_installer&view=database',
				'enabled' => false,
				'title'   => 'MOD_MENU_INSTALLER_SUBMENU_DATABASE',
				'label'   => 'MOD_MENU_INSTALLER_SUBMENU_DATABASE',
				'desc'    => 'MOD_MENU_INSTALLER_SUBMENU_DATABASE',
				'icon'    => 'refresh'
			],
			'warnings' => [
				'link'    => 'index.php?option=com_installer&view=warnings',
				'enabled' => false,
				'title'   => 'MOD_MENU_INSTALLER_SUBMENU_WARNINGS',
				'label'   => 'MOD_MENU_INSTALLER_SUBMENU_WARNINGS',
				'desc'    => 'MOD_MENU_INSTALLER_SUBMENU_WARNINGS',
				'icon'    => 'refresh'
			],

			// Plugins
			'com_plugins' => [
				'link'    => 'index.php?option=com_plugins',
				'enabled' => false,
				'title'   => 'MOD_MENU_EXTENSIONS_PLUGIN_MANAGER',
				'label'   => 'MOD_MENU_EXTENSIONS_PLUGIN_MANAGER',
				'desc'    => 'MOD_MENU_EXTENSIONS_PLUGIN_MANAGER',
				'icon'    => 'cog'
			],

			// Templates
			'com_templates' => [
				'link'    => 'index.php?option=com_templates',
				'enabled' => false,
				'title'   => 'MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER',
				'label'   => 'MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER',
				'desc'    => 'MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER',
				'icon'    => 'image'
			],
			'com_templates_styles' => [
				'link'    => 'index.php?option=com_templates&view=styles',
				'enabled' => false,
				'title'   => 'MOD_MENU_COM_TEMPLATES_SUBMENU_STYLES',
				'label'   => 'MOD_MENU_COM_TEMPLATES_SUBMENU_STYLES',
				'desc'    => 'MOD_MENU_COM_TEMPLATES_SUBMENU_STYLES',
				'icon'    => 'image'
			],
			'com_templates_edit' => [
				'link'    => 'index.php?option=com_templates&view=templates',
				'enabled' => false,
				'title'   => 'MOD_MENU_COM_TEMPLATES_SUBMENU_TEMPLATES',
				'label'   => 'MOD_MENU_COM_TEMPLATES_SUBMENU_TEMPLATES',
				'desc'    => 'MOD_MENU_COM_TEMPLATES_SUBMENU_TEMPLATES',
				'icon'    => 'edit'
			],

			// Languages
			'com_languages' => [
				'link'    => 'index.php?option=com_languages',
				'enabled' => false,
				'title'   => 'MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER',
				'label'   => 'MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER',
				'desc'    => 'MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER',
				'icon'    => 'cog'
			],
			'com_languages_installed' => [
				'link'    => 'index.php?option=com_languages&view=installed',
				'enabled' => false,
				'title'   => 'MOD_MENU_COM_LANGUAGES_SUBMENU_INSTALLED',
				'label'   => 'MOD_MENU_COM_LANGUAGES_SUBMENU_INSTALLED',
				'desc'    => 'MOD_MENU_COM_LANGUAGES_SUBMENU_INSTALLED',
				'icon'    => 'cog'
			],
			'com_languages_content' => [
				'link'    => 'index.php?option=com_languages&view=languages',
				'enabled' => false,
				'title'   => 'MOD_MENU_COM_LANGUAGES_SUBMENU_CONTENT',
				'label'   => 'MOD_MENU_COM_LANGUAGES_SUBMENU_CONTENT',
				'desc'    => 'MOD_MENU_COM_LANGUAGES_SUBMENU_CONTENT',
				'icon'    => 'cog'
			],
			'com_languages_overrides' => [
				'link'    => 'index.php?option=com_languages&view=overrides',
				'enabled' => false,
				'title'   => 'MOD_MENU_COM_LANGUAGES_SUBMENU_OVERRIDES',
				'label'   => 'MOD_MENU_COM_LANGUAGES_SUBMENU_OVERRIDES',
				'desc'    => 'MOD_MENU_COM_LANGUAGES_SUBMENU_OVERRIDES',
				'icon'    => 'cog'
			],
			'com_languages_install' => [
				'link'    => 'index.php?option=com_installer&view=languages',
				'enabled' => false,
				'title'   => 'MOD_MENU_INSTALLER_SUBMENU_LANGUAGES',
				'label'   => 'MOD_MENU_INSTALLER_SUBMENU_LANGUAGES',
				'desc'    => 'MOD_MENU_INSTALLER_SUBMENU_LANGUAGES',
				'icon'    => 'cog'
			],

			// Extensions
			'com_installer_manage' => [
				'link'    => 'index.php?option=com_installer&view=manage',
				'enabled' => false,
				'title'   => 'MOD_MENU_INSTALLER_SUBMENU_MANAGE',
				'label'   => 'MOD_MENU_INSTALLER_SUBMENU_MANAGE',
				'desc'    => 'MOD_MENU_INSTALLER_SUBMENU_MANAGE',
				'icon'    => 'cog'
			],
			'com_installer_discover' => [
				'link'    => 'index.php?option=com_installer&view=discover',
				'enabled' => false,
				'title'   => 'MOD_MENU_INSTALLER_SUBMENU_DISCOVER',
				'label'   => 'MOD_MENU_INSTALLER_SUBMENU_DISCOVER',
				'desc'    => 'MOD_MENU_INSTALLER_SUBMENU_DISCOVER',
				'icon'    => 'cog'
			],

			// Updates
			'com_joomlaupdate' => [
				'link'    => 'index.php?option=com_installer&view=manage',
				'enabled' => false,
				'title'   => 'System update',
				'label'   => 'System update',
				'desc'    => 'System update',
				'icon'    => 'upload'
			],
			'extensions_update' => [
				'link'    => 'index.php?option=com_installer&view=update',
				'enabled' => false,
				'title'   => 'MOD_MENU_INSTALLER_SUBMENU_UPDATE',
				'label'   => 'MOD_MENU_INSTALLER_SUBMENU_UPDATE',
				'desc'    => 'MOD_MENU_INSTALLER_SUBMENU_UPDATE',
				'icon'    => 'upload'
			],
			'update_sites' => [
				'link'    => 'index.php?option=com_installer&view=updatesites',
				'enabled' => false,
				'title'   => 'MOD_MENU_INSTALLER_SUBMENU_UPDATESITES',
				'label'   => 'MOD_MENU_INSTALLER_SUBMENU_UPDATESITES',
				'desc'    => 'MOD_MENU_INSTALLER_SUBMENU_UPDATESITES',
				'icon'    => 'edit'
			],

		];


		if ($user->authorise('core.admin'))
		{
			$links['com_config']['enabled'] = true;
			$links['sysinfo']['enabled']    = true;
			static::$notEmpty               = true;
		}

		if ($user->authorise('core.manage', 'com_checkin'))
		{
			$links['com_checkin']['enabled'] = true;
			static::$notEmpty                = true;
		}

		if ($user->authorise('core.manage', 'com_cache'))
		{
			$links['com_cache']['enabled']       = true;
			$links['com_cache_purge']['enabled'] = true;
			static::$notEmpty                    = true;
		}

		if ($user->authorise('core.manage', 'com_postinstall'))
		{
			$links['com_postinstall']['enabled'] = true;
			static::$notEmpty                    = true;
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			$links['database']['enabled']               = true;
			$links['warnings']['enabled']               = true;
			$links['com_languages_install']['enabled']  = true;
			$links['com_installer_manage']['enabled']   = true;
			$links['com_installer_discover']['enabled'] = true;
			$links['extensions_update']['enabled']      = true;
			$links['update_sites']['enabled']           = true;
			static::$notEmpty                           = true;
		}

		if ($user->authorise('core.manage', 'com_plugins'))
		{
			$links['com_plugins']['enabled'] = true;
			static::$notEmpty                = true;
		}

		if ($user->authorise('core.manage', 'com_templates'))
		{
			$links['com_templates']['enabled']        = true;
			$links['com_templates_styles']['enabled'] = true;
			$links['com_templates_edit']['enabled']   = true;
			static::$notEmpty                         = true;
		}

		if ($user->authorise('core.manage', 'com_languages'))
		{
			$links['com_languages']['enabled'] = true;
			static::$notEmpty                  = true;
		}

		if ($user->authorise('core.manage', 'com_joomlaupdate'))
		{
			$links['com_joomlaupdate']['enabled'] = true;
			static::$notEmpty                     = true;
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
		$language_tag = JFactory::getLanguage()->getTag();;
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
		JToolbarHelper::title(JText::_('System Panel'), 'cog help_header');
	}
}
