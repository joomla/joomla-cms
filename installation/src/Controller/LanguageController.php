<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Table\Table;

/**
 * Language controller class for the Joomla Installer.
 *
 * @since  3.1
 */
class LanguageController extends JSONController
{
	/**
	 * Sets the language.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function set()
	{
		$this->checkValidToken();

		// Check for potentially unwritable session
		$session = $this->app->getSession();

		if ($session->isNew())
		{
			$this->sendJsonResponse(new \Exception(\JText::_('INSTL_COOKIES_NOT_ENABLED'), 500));
		}

		/** @var SetupModel $model */
		$model = $this->getModel('Setup');

		// Get the posted values from the request and validate them.
		$data   = $this->input->post->get('jform', [], 'array');
		$return = $model->validate($data, 'language');

		$r = new \stdClass;

		// Check for validation errors.
		if ($return === false)
		{
			/*
			 * The validate method enqueued all messages for us, so we just need to
			 * redirect back to the site setup screen.
			 */
			$r->view = $this->input->getWord('view', 'setup');
			$this->sendJsonResponse($r);
		}

		// Store the options in the session.
		$model->storeOptions($return);

		// Setup language
		Factory::$language = Language::getInstance($return['language']);

		// Redirect to the page.
		$r->view = $this->input->getWord('view', 'setup');

		$this->sendJsonResponse($r);
	}

	/**
	 * Sets the default language.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setdefault()
	{
		$this->checkValidToken();

		$app = $this->app;

		// Get the languages model.
		$model = $this->getModel('Languages');

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
			$this->app->enqueueMessage(\JText::_('INSTL_DEFAULTLANGUAGE_ADMIN_COULDNT_SET_DEFAULT'), 'error');
		}
		else
		{
			// Create a response body.
			$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_ADMIN_SET_DEFAULT', $admin_lang), 'message');
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
			$app->enqueueMessage(\JText::_('INSTL_DEFAULTLANGUAGE_FRONTEND_COULDNT_SET_DEFAULT'), 'error');
		}
		else
		{
			// Create a response body.
			$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_FRONTEND_SET_DEFAULT', $frontend_lang), 'message');
		}

		// Check if user has activated the multilingual site
		$data = $this->getInput()->post->get('jform', array(), 'array');

		if ((int) $data['activateMultilanguage'])
		{
			if (!$model->enablePlugin('plg_system_languagefilter'))
			{
				$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_PLG_LANGUAGEFILTER', $frontend_lang), 'warning');
			}

			// Activate optional ISO code Plugin
			$activatePluginIsoCode = (int) $data['activatePluginLanguageCode'];

			if ($activatePluginIsoCode && !$model->enablePlugin('plg_system_languagecode'))
			{
				$app->enqueueMessage(\JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_PLG_LANGUAGECODE'), 'warning');
			}

			if (!$model->addModuleLanguageSwitcher())
			{
				$app->enqueueMessage(\JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_ENABLE_MODULESWHITCHER_LANGUAGECODE'), 'warning');
			}

			// Add menus
			\JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables/');

			$siteLanguages       = $model->getInstalledlangsFrontend();
			$groupedAssociations = array();

			foreach ($siteLanguages as $siteLang)
			{
				if (!$model->addMenuGroup($siteLang))
				{
					$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU', $siteLang->name), 'warning');

					continue;
				}

				if (!$data['installLocalisedContent'])
				{
					if (!$tableMenuItem = $model->addFeaturedMenuItem($siteLang))
					{
						$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU_ITEM', $siteLang->name), 'warning');

						continue;
					}

					$groupedAssociations['com_menus.item'][$siteLang->language] = $tableMenuItem->id;
				}

				if (!$tableMenuItem = $model->addAllCategoriesMenuItem($siteLang))
				{
					$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU_ITEM', $siteLang->name), 'warning');

					continue;
				}

				$groupedAssociations['com_menus.item'][$siteLang->language] = $tableMenuItem->id;

				if (!$model->addModuleMenu($siteLang))
				{
					$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU_MODULE', $frontend_lang), 'warning');

					continue;
				}

				if ((int) $data['installLocalisedContent'])
				{
					if (!$tableCategory = $model->addCategory($siteLang))
					{
						$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_CATEGORY', $frontend_lang), 'warning');

						continue;
					}

					$groupedAssociations['com_categories.item'][$siteLang->language] = $tableCategory->id;

					if (!$tableMenuItem = $model->addBlogMenuItem($siteLang, $tableCategory->id))
					{
						$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_MENU_ITEM', $siteLang->name), 'warning');

						continue;
					}

					$groupedAssociations['com_menus.item'][$siteLang->language] = $tableMenuItem->id;

					if (!$tableArticle = $model->addArticle($siteLang, $tableCategory->id))
					{
						$app->enqueueMessage(\JText::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_ARTICLE', $frontend_lang), 'warning');

						continue;
					}

					$groupedAssociations['com_content.item'][$siteLang->language] = $tableArticle->id;
				}
			}

			if (!$model->addAssociations($groupedAssociations))
			{
				$app->enqueueMessage(\JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_ADD_ASSOCIATIONS'), 'warning');
			}

			if (!$model->disableModuleMainMenu())
			{
				$app->enqueueMessage(\JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_UNPUBLISH_MOD_DEFAULTMENU'), 'warning');
			}

			if (!$model->enableModule('mod_multilangstatus'))
			{
				$app->enqueueMessage(\JText::_('INSTL_DEFAULTLANGUAGE_COULD_NOT_PUBLISH_MOD_MULTILANGSTATUS'), 'warning');
			}
		}

		$r = new \stdClass;

		// Redirect to the final page.
		$r->view = 'remove';
		$app->sendJsonResponse($r);
	}
}
