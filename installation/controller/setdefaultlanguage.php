<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller class to set the default application languages for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerSetdefaultlanguage extends JControllerBase
{
	/**
	 * Constructor.
	 *
	 * @since   3.1
	 */
	public function __construct()
	{
		parent::__construct();

		// Overrides application config and set the configuration.php file so tokens and database works
		JFactory::$config = null;
		JFactory::getConfig(JPATH_SITE . '/configuration.php');
	}

	/**
	 * Execute the controller.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function execute()
	{
		// Get the application
		/* @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the languages model.
		$model = new InstallationModelLanguages;

		// Check for request forgeries in the administrator language
		$admin_lang = $this->input->getString('administratorlang', false);

		// Check that the string is an ISO Language Code avoiding any injection.
		if (!preg_match('/^[a-z]{2}(\-[A-Z]{2})?$/', $admin_lang))
		{
			$admin_lang = 'en-GB';
		}

		// Attempt to set the default administrator language
		if (!$model->setDefault($admin_lang, 'administrator'))
		{
			// Create an error response message.
			$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_ADMIN_COULDNT_SET_DEFAULT'), 'error');
		}
		else
		{
			// Create a response body.
			$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_ADMIN_SET_DEFAULT', $admin_lang), 'message');
		}

		// Check for request forgeries in the site language
		$frontend_lang = $this->input->getString('frontendlang', false);

		// Check that the string is an ISO Language Code avoiding any injection.
		if (!preg_match('/^[a-z]{2}(\-[A-Z]{2})?$/', $frontend_lang))
		{
			$frontend_lang = 'en-GB';
		}

		// Attempt to set the default site language
		if (!$model->setDefault($frontend_lang, 'site'))
		{
			// Create an error response message.
			$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_FRONTEND_COULDNT_SET_DEFAULT'), 'error');
		}
		else
		{
			// Create a response body.
			$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_FRONTEND_SET_DEFAULT', $frontend_lang), 'message');
		}

		// Check if user has activated the multilingual site
		$data = $this->input->post->get('jform', array(), 'array');

		if ((int) $data['activateMultilanguage'])
		{
			if (!$model->enablePlugin('plg_system_languagefilter'))
			{
				$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_PLG_LANGUAGEFILTER', $frontend_lang), 'warning');
			}

			// Activate optional ISO code Plugin
			$activatePluginIsoCode = (int) $data['activatePluginLanguageCode'];

			if ($activatePluginIsoCode && !$model->enablePlugin('plg_system_languagecode'))
			{
				$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_PLG_LANGUAGECODE'), 'warning');
			}

			if (!$model->addModuleLanguageSwitcher())
			{
				$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_MODULESWHITCHER_LANGUAGECODE'), 'warning');
			}

			// Add menus
			JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables/');

			$siteLanguages       = $model->getInstalledlangsFrontend();
			$groupedAssociations = array();

			foreach ($siteLanguages as $siteLang)
			{
				if (!$model->addMenuGroup($siteLang))
				{
					$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU', $siteLang->name), 'warning');

					continue;
				}

				if (!$tableMenuItem = $model->addMenuItem($siteLang))
				{
					$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU_ITEM', $siteLang->name), 'warning');

					continue;
				}

				$groupedAssociations['com_menus.item'][$siteLang->language] = $tableMenuItem->id;

				if (!$model->addModuleMenu($siteLang))
				{
					$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU_MODULE', $frontend_lang), 'warning');

					continue;
				}

				if ((int) $data['installLocalisedContent'])
				{
					if (!$tableCategory = $model->addCategory($siteLang))
					{
						$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_CATEGORY', $frontend_lang), 'warning');

						continue;
					}

					$groupedAssociations['com_categories.item'][$siteLang->language] = $tableCategory->id;

					if (!$tableArticle = $model->addArticle($siteLang, $tableCategory->id))
					{
						$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_ARTICLE', $frontend_lang), 'warning');

						continue;
					}

					$groupedAssociations['com_content.item'][$siteLang->language] = $tableArticle->id;
				}
			}

			if (!$model->addAssociations($groupedAssociations))
			{
				$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_ADD_ASSOCIATIONS'), 'warning');
			}

			if (!$model->disableModuleMainMenu())
			{
				$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_UNPUBLISH_MOD_DEFAULTMENU'), 'warning');
			}

			if (!$model->enableModule('mod_multilangstatus'))
			{
				$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_PUBLISH_MOD_MULTILANGSTATUS'), 'warning');
			}
		}

		$r = new stdClass;

		// Redirect to the final page.
		$r->view = 'remove';
		$app->sendJsonResponse($r);
	}
}
