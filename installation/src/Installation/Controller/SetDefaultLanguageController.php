<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Installation\Controller;

defined('_JEXEC') or die;

use JText,
	JTable,
	JLoader,
	JFactory,
	JSession,
	JControllerBase;

use Installation\Model\LanguagesModel;

/**
 * Controller class to set the default application languages for the Joomla Installer.
 *
 * @package     Joomla.Installation
 * @subpackage  Controller
 * @since       3.1
 */
class SetDefaultLanguageController extends JControllerBase
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
		JFactory::getConfig($this->getApplication()->get('configurationPath'));
		JFactory::$session = null;
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
		/* @var $app \Installation\Application\WebApplication */
		$app = $this->getApplication();
		$administratorPath = $app->get('administratorPath');

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new \Exception(JText::_('JINVALID_TOKEN'), 403));

		$state = new \JRegistry;
		$state->set('configurationPath', $app->get('configurationPath'));
		$state->set('administratorPath', $app->get('administratorPath'));

		// Get the languages model.
		$model = new LanguagesModel($state);

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
			// Create a error response message.
			$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_ADMIN_COULDNT_SET_DEFAULT'), 'error');
		}
		else
		{
			// Create a response body.
			$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_ADMIN_SET_DEFAULT', $admin_lang));
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
			// Create a error response message.
			$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_FRONTEND_COULDNT_SET_DEFAULT'), 'error');
		}
		else
		{
			// Create a response body.
			$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_FRONTEND_SET_DEFAULT', $frontend_lang));
		}

		// Check if user has activated the multilingual site
		$data                = $this->input->post->get('jform', array(), 'array');
		$activeMultilanguage = (int) $data['activateMultilanguage'];

		if ($activeMultilanguage)
		{
			if (!$model->enablePlugin('plg_system_languagefilter'))
			{
				$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_PLG_LANGUAGEFILTER', $frontend_lang));
			}

			// Activate optional ISO code Plugin
			$activatePluginIsoCode = (int) $data['activatePluginLanguageCode'];

			if ($activatePluginIsoCode)
			{
				if (!$model->enablePlugin('plg_system_languagecode'))
				{
					$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_PLG_LANGUAGECODE'));
				}
			}

			if (!$model->addModuleLanguageSwitcher())
			{
				$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_MODULESWHITCHER_LANGUAGECODE'));
			}

			// Add menus
			JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');
			JTable::addIncludePath($administratorPath . '/components/com_menus/tables/');

			$siteLanguages = $model->getInstalledlangsFrontend();

			foreach ($siteLanguages as $siteLang)
			{
				$error = false;

				// Add Language Manager: Content Languages
				$tableLanguage = JTable::getInstance('Language');

				// Search if just added
				$return = $tableLanguage->load(array('lang_code' => $siteLang->language));

				if ($return === false)
				{
					$sefLangString = $model->getSefString($siteLang, $siteLanguages);

					if (!$model->addLanguage($siteLang, $sefLangString))
					{
						$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_CONTENT_LANGUAGE', $siteLang->name));
						$error = true;
					}
				}

				if (!$error && !$model->addMenuGroup($siteLang))
				{
					$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU', $siteLang->name));
					$error = true;
				}

				if (!$error && !$model->addMenuItem($siteLang))
				{
					$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU_ITEM', $siteLang->name));
					$error = true;
				}

				if (!$error && !$model->addModuleMenu($siteLang))
				{
					$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU_MODULE', $frontend_lang));
					$error = true;
				}

				if (!$error)
				{
					$tableCategory = $model->addCategory($siteLang);

					if ($tableCategory === false)
					{
						$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_CATEGORY', $frontend_lang));
						$error = true;
					}
				}

				if (!$error)
				{
					$categoryId = $tableCategory->id;

					if (!$model->addArticle($siteLang, $categoryId))
					{
						$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_ARTICLE', $frontend_lang));
						$error = true;
					}
				}
			}

			if (!$model->disableModuleMainMenu())
			{
				$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_UNPUBLISH_MOD_DEFAULTMENU'));
			}

			if (!$model->enableModule('mod_multilangstatus'))
			{
				$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_PUBLISH_MOD_MULTILANGSTATUS'));
			}
		}

		$r = new \stdClass;

		// Redirect to the final page.
		$r->view = 'remove';
		$app->sendJsonResponse($r);
	}
}
