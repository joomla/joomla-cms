<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once dirname(dirname(__DIR__)) . '/helper/component.php';

/**
 * View for the component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 */
class ConfigViewComponent extends JViewLegacy
{
	/**
	 * Associates the options screen help key with the component name.
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $helpScreenArray = array(
		'com_banners' => 'JHELP_COMPONENTS_BANNER_MANAGER_OPTIONS',
		'com_cache' => 'JHELP_COMPONENTS_CACHE_MANAGER_SETTINGS',
		'com_checkin' => 'JHELP_COMPONENTS_CHECK-IN_CONFIGURATION',
		'com_contact' => 'JHELP_COMPONENTS_CONTACT_MANAGER_OPTIONS',
		'com_content' => 'JHELP_COMPONENTS_ARTICLE_MANAGER_OPTIONS',
		'com_finder' => 'JHELP_COMPONENTS_SMART_SEARCH_CONFIGURATION',
		'com_installer' => 'JHELP_COMPONENTS_INSTALLER_CONFIGURATION',
		'com_joomlaupdate' => 'JHELP_COMPONENTS_JOOMLA_UPDATE_CONFIGURATION',
		'com_languages' => 'JHELP_COMPONENTS_LANGUAGE_MANAGER_OPTIONS',
		'com_media' => 'JHELP_COMPONENTS_MEDIA_MANAGER_OPTIONS',
		'com_menus' => 'JHELP_COMPONENTS_MENUS_CONFIGURATION',
		'com_messages' => 'JHELP_COMPONENTS_MESSAGES_CONFIGURATION',
		'com_modules' => 'JHELP_COMPONENTS_MODULE_MANAGER_OPTIONS',
		'com_newsfeeds' => 'JHELP_COMPONENTS_NEWS_FEED_MANAGER_OPTIONS',
		'com_plugins' => 'JHELP_COMPONENTS_PLUG-IN_MANAGER_OPTIONS',
		'com_redirect' => 'JHELP_COMPONENTS_REDIRECT_MANAGER_OPTIONS',
		'com_search' => 'JHELP_COMPONENTS_SEARCH_MANAGER_OPTIONS',
		'com_tags' => 'JHELP_COMPONENTS_TAGS_MANAGER_OPTIONS',
		'com_templates' => 'JHELP_COMPONENTS_TEMPLATE_MANAGER_OPTIONS',
		'com_users' => 'JHELP_COMPONENTS_USERS_CONFIGURATION',
		'com_weblinks' => 'JHELP_COMPONENTS_WEB_LINKS_MANAGER_OPTIONS',
	);

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise a Error object.
	 *
	 * @since   1.5
	 */
	public function display($tpl = null)
	{
		$form		= $this->get('Form');
		$component	= $this->get('Component');
		$user       = JFactory::getUser();
		$app        = JFactory::getApplication();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the form to the data.
		if ($form && $component->params)
		{
			$form->bind($component->params);
		}

		$this->form = &$form;
		$this->component = &$component;

		$this->components = ConfigHelperComponent::getComponentsWithConfig();
		ConfigHelperComponent::loadLanguageForComponents($this->components);

		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->currentComponent = JFactory::getApplication()->input->get('component');
		$this->return = $app->input->get('return', '', 'base64');

		$this->addToolbar();
		parent::display($tpl);
		$app->input->set('hidemainmenu', true);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_($this->component->option . '_configuration'), 'config.png');
		JToolbarHelper::apply('component.apply');
		JToolbarHelper::save('component.save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('component.cancel');
		JToolbarHelper::divider();

		// Get the correct help key for this screen
		if (isset($this->helpScreenArray[$this->component->option]))
		{
			JToolbarHelper::help($this->helpScreenArray[$this->component->option]);
		}
		else
		{
			JToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
		}
	}
}
