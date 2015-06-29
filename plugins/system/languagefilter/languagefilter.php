<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagefilter
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
JLoader::register('MultilangstatusHelper', JPATH_ADMINISTRATOR . '/components/com_languages/helpers/multilangstatus.php');

/**
 * Joomla! Language Filter Plugin.
 *
 * @since  1.6
 */
class PlgSystemLanguageFilter extends JPlugin
{
	protected $mode_sef;

	protected $sefs;

	protected $lang_codes;

	protected $current_lang;

	protected $default_lang;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.3
	 */
	protected $app;

	private $home_pages;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app = JFactory::getApplication();

		if ($this->app->isSite())
		{
			// Setup language data.
			$this->mode_sef		= $this->app->get('sef', 0);
			$this->sefs			= JLanguageHelper::getLanguages('sef');
			$this->lang_codes	= JLanguageHelper::getLanguages('lang_code');
			$this->default_lang	= JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			$this->home_pages = MultilangstatusHelper::getHomepages();

			$levels = JFactory::getUser()->getAuthorisedViewLevels();
			$site_langs = MultilangstatusHelper::getSitelangs();

			foreach ($this->sefs as $sef => $language)
			{
				// Check access and if frontend language exists and is enabled
				// @todo: In Joomla 2.5.4 and earlier access wasn't set. Non modified Content Languages got 0 as access value
				if (($language->access && !in_array($language->access, $levels))
					|| !array_key_exists($language->lang_code, $site_langs)
					|| !is_dir(JPATH_SITE . '/language/' . $language->lang_code))
				{
					unset($this->lang_codes[$language->lang_code]);
					unset($this->sefs[$language->sef]);
				}
			}

			$this->app->setLanguageFilter(true);

			// Detect browser feature.
			$this->app->setDetectBrowser($this->params->get('detect_browser', '1') == '1');
		}
	}

	/**
	 * After initialise.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onAfterInitialise()
	{
		$this->app->item_associations = $this->params->get('item_associations', 0);

		if ($this->app->isSite())
		{
			$router = $this->app->getRouter();

			// Attach build rules for language SEF.
			$router->attachBuildRule(array($this, 'preprocessBuildRule'), JRouter::PROCESS_BEFORE);
			$router->attachBuildRule(array($this, 'buildRule'), JRouter::PROCESS_DURING);

			if ($this->mode_sef)
			{
				$router->attachBuildRule(array($this, 'postprocessSEFBuildRule'), JRouter::PROCESS_AFTER);
			}
			else
			{
				$router->attachBuildRule(array($this, 'postprocessNonSEFBuildRule'), JRouter::PROCESS_AFTER);
			}

			// Attach parse rules for language SEF.
			$router->attachParseRule(array($this, 'parseRule'), JRouter::PROCESS_DURING);
		}
	}

	/**
	 * After route.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function onAfterRoute()
	{
		// Add custom site name.
		if (isset($this->lang_codes[$this->current_lang]) && $this->lang_codes[$this->current_lang]->sitename)
		{
			$this->app->set('sitename', $this->lang_codes[$this->current_lang]->sitename);
		}
	}

	/**
	 * Add build preprocess rule to router.
	 *
	 * @param   JRouter  &$router  JRouter object.
	 * @param   JUri     &$uri     JUri object.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function preprocessBuildRule(&$router, &$uri)
	{
		$lang = $uri->getVar('lang', $this->current_lang);
		$uri->setVar('lang', $lang);

		if (isset($this->sefs[$lang]))
		{
			$lang = $this->sefs[$lang]->lang_code;
			$uri->setVar('lang', $lang);
		}
	}

	/**
	 * Add build rule to router.
	 *
	 * @param   JRouter  &$router  JRouter object.
	 * @param   JUri     &$uri     JUri object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function buildRule(&$router, &$uri)
	{
		$lang = $uri->getVar('lang');

		if (isset($this->lang_codes[$lang]))
		{
			$sef = $this->lang_codes[$lang]->sef;
		}
		else
		{
			$sef = $this->lang_codes[$this->current_lang]->sef;
		}

		if ($this->mode_sef
			&& (!$this->params->get('remove_default_prefix', 0)
			|| $lang != $this->default_lang
			|| $lang != $this->current_lang))
		{
			$uri->setPath($uri->getPath() . '/' . $sef . '/');
		}
	}

	/**
	 * postprocess build rule for SEF URLs
	 *
	 * @param   JRouter  &$router  JRouter object.
	 * @param   JUri     &$uri     JUri object.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function postprocessSEFBuildRule(&$router, &$uri)
	{
		$uri->delVar('lang');
	}

	/**
	 * postprocess build rule for non-SEF URLs
	 *
	 * @param   JRouter  &$router  JRouter object.
	 * @param   JUri     &$uri     JUri object.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function postprocessNonSEFBuildRule(&$router, &$uri)
	{
		$lang = $uri->getVar('lang');

		if (isset($this->lang_codes[$lang]))
		{
			$uri->setVar('lang', $this->lang_codes[$lang]->sef);
		}
	}

	/**
	 * Add parse rule to router.
	 *
	 * @param   JRouter  &$router  JRouter object.
	 * @param   JUri     &$uri     JUri object.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function parseRule(&$router, &$uri)
	{
		$found = false;
		$lang_code = false;

		// Are we in SEF mode or not?
		if ($this->mode_sef)
		{
			// Code for SEF

			$parts = explode('/', $uri->getPath());
			$sef = $parts[0];

			// If we remove the default prefix AND $sef (i.e: parts[0]) is not a valid sef,
			// then we either have an URL for the default language or we are on the naked domain
			if ($this->params->get('remove_default_prefix', 0) && !isset($this->sefs[$sef]))
			{
				// If $parts[0] exist, we have an URL for the default language
				if ($parts[0])
				{
					$lang_code = $this->default_lang;
				}
				else
				{
					// $parts[0] empty, then we are on the naked domain
					// and we must see what the user preference (cookie/browser) is
					$lang_code = $this->getClientLanguage();
				}

				// In any case, if the language is the default language there is need to redirect)
				if ($lang_code == $this->default_lang)
				{
					$found = true;
				}
			}
			else
			{
				// We drop here if "Remove default prefix" is not set
				// We should have a have a valid sef, but we must test
				if (isset($this->sefs[$sef]))
				{
					$lang_code = $this->sefs[$sef]->lang_code;
					$found = true;

					// We remove the sef from the path
					array_shift($parts);
					$uri->setPath(implode('/', $parts));
				}

				// If we have the default language sef and we have "Remove default prefix" set...
				if ($lang_code == $this->default_lang && $this->params->get('remove_default_prefix', 0))
				{
					// We set $found to false (so we'll redirect) and set a cookie for the default language.
					$found = false;
					$this->setLanguageCookie($lang_code);
				}
			}
		}
		else
		{
			// Code for non-SEF

			$lang = $uri->getVar('lang');
			if (isset($this->sefs[$lang]))
			{
				$found = true;
				$lang_code = $this->sefs[$lang]->lang_code;
			}
		}

		// We are called via POST. We don't care about the language and simply set the default language as our current language.
		// [Errr... this is not exactly what happens here below!!! TBD verify...]
		if ($this->app->input->getMethod() == "POST"
			|| count($this->app->input->post) > 0
			|| count($this->app->input->files) > 0)
		{
			if (!isset($lang_code))
			{
				$lang_code = $this->getClientLanguage();
			}
			$found = true;
		}

		// We have not found the language and thus we redirect
		if (!$found)
		{
			// Do we need this?
			if (!isset($lang_code) || !isset($this->lang_codes[$lang_code]))
			{
				$lang_code = $this->getClientLanguage();
			}

			// Time to redirect...
			if ($this->mode_sef)
			{
				// If this is not the default language OR we don't remove the sef for the default language, let's add the sef back to the path.
				if ($lang_code != $this->default_lang || !$this->params->get('remove_default_prefix', 0))
				{
					$uri->setPath($this->lang_codes[$lang_code]->sef . '/' . $uri->getPath());
				}

				// Add index.php if we don't use URL rewriting
				if (!$this->app->get('sef_rewrite'))
				{
					$uri->setPath('index.php/' . $uri->getPath());
				}

				$this->app->redirect($uri->base() . $uri->toString(array('path', 'query', 'fragment')));
			}
			else
			{
				$uri->setVar('lang', $this->lang_codes[$lang_code]->sef);
				$this->app->redirect($uri->base() . 'index.php?' . $uri->getQuery());
			}
		}

		// We have found our language and now need to set the cookie and the language value in our system
		$this->current_lang = $lang_code;

		// Set the request var.
		$this->app->input->set('language', $lang_code);
		$this->app->set('language', $lang_code);

		// If the language is not the default language we do some stuff...
		$language = JFactory::getLanguage();
		if ($language->getTag() != $lang_code)
		{
			$newLang = JLanguage::getInstance($lang_code);

			foreach ($language->getPaths() as $extension => $files)
			{
				$newLang->load($extension);
			}

			JFactory::$language = $newLang;
			$this->app->loadLanguage($newLang);
		}

		// Create a cookie.
		if ($this->getClientLanguage() != $lang_code)
		{
			$this->setLanguageCookie($lang_code);
		}

		return array('lang' => $lang_code);
	}

	/**
	 * Before store user method.
	 *
	 * Method is called before user data is stored in the database.
	 *
	 * @param   array    $user   Holds the old user data.
	 * @param   boolean  $isnew  True if a new user is stored.
	 * @param   array    $new    Holds the new user data.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onUserBeforeSave($user, $isnew, $new)
	{
		if ($this->app->isSite() && $this->params->get('automatic_change', '1') == '1' && key_exists('params', $user))
		{
			$registry = new Registry;
			$registry->loadString($user['params']);
			$this->user_lang_code = $registry->get('language');

			if (empty($this->user_lang_code))
			{
				$this->user_lang_code = $this->current_lang;
			}
		}
	}

	/**
	 * After store user method.
	 *
	 * Method is called after user data is stored in the database.
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isnew    True if a new user is stored.
	 * @param   boolean  $success  True if user was succesfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		if ($this->app->isSite() && $this->params->get('automatic_change', '1') == '1' && key_exists('params', $user) && $success)
		{
			$registry = new Registry;
			$registry->loadString($user['params']);
			$lang_code = $registry->get('language');

			if (empty($lang_code))
			{
				$lang_code = $this->current_lang;
			}

			if ($lang_code == $this->user_lang_code || !isset($this->lang_codes[$lang_code]))
			{
				$this->app->setUserState('com_users.edit.profile.redirect', null);
			}
			else
			{
				$this->app->setUserState('com_users.edit.profile.redirect', 'index.php?Itemid='
					. $this->app->getMenu()->getDefault($lang_code)->id . '&lang=' . $this->lang_codes[$lang_code]->sef
				);

				// Create a cookie.
				$this->setLanguageCookie($lang_code);
			}
		}
	}

	/**
	 * Method to handle any login logic and report back to the subject.
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (remember, autoregister, group).
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.5
	 */
	public function onUserLogin($user, $options = array())
	{
		$menu = $this->app->getMenu();

		if ($this->app->isSite() && $this->params->get('automatic_change', 1))
		{

			$lang_code = $user['language'];

			// If no language is specified for this user, we set it to the site default language
			if (empty($lang_code))
			{
				$lang_code = $this->default_lang;
			}

			// We must be sure to have a valid language code
			if (!$this->checkLanguage($lang_code))
			{
				$lang_code = $this->getClientLanguage();
			}

			// Try to get association from the current active menu item
			$active = $menu->getActive();
			$foundAssociation = false;

			if ($active)
			{
				if (JLanguageAssociations::isEnabled())
				{
					$associations = MenusHelper::getAssociations($active->id);
				}

				if (isset($associations[$lang_code]) && $menu->getItem($associations[$lang_code]))
				{
					$associationItemid = $associations[$lang_code];
					$this->app->setUserState('users.login.form.return', 'index.php?Itemid=' . $associationItemid);
					$foundAssociation = true;
				}
				elseif ($active->home)
				{
					// We are on a Home page, we redirect to the user site language home page
					$item = $menu->getDefault($lang_code);

					if ($item && $item->language != $active->language && $item->language != '*')
					{
						$this->app->setUserState('users.login.form.return', 'index.php?Itemid=' . $item->id);
						$foundAssociation = true;
					}
				}
			}

			if ($foundAssociation && $lang_code != $this->current_lang)
			{
				// Change language.
				$this->current_lang = $lang_code;

				// Create a cookie.
				$this->setLanguageCookie($lang_code);

				// Change the language code.
				JFactory::getLanguage()->setLanguage($lang_code);
			}
		}
	}

	/**
	 * Method to add alternative meta tags for associated menu items.
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public function onAfterDispatch()
	{
		$doc = JFactory::getDocument();

		if ($this->app->isSite() && $this->params->get('alternate_meta') && $doc->getType() == 'html' && count($this->lang_codes) > 1)
		{
			$languages = $this->lang_codes;
			$menu = $this->app->getMenu();
			$active = $menu->getActive();
			$remove_default_prefix = $this->params->get('remove_default_prefix', 0);
			$server = JUri::getInstance()->toString(array('scheme', 'host', 'port'));
			$is_home = false;

			if ($active)
			{
				$active_link = JRoute::_($active->link . '&Itemid=' . $active->id, false);
				$current_link = JUri::getInstance()->toString(array('path', 'query'));

				// Load menu associations
				if ($active_link == $current_link)
				{
					$associations = MenusHelper::getAssociations($active->id);
				}

				// Check if we are on the homepage
				$is_home = ($active->home
					&& ($active_link == $current_link || $active_link == $current_link . 'index.php' || $active_link . '/' == $current_link));
			}

			// Load component associations.
			$option = $this->app->input->get('option');
			$cName = JString::ucfirst(JString::str_ireplace('com_', '', $option)) . 'HelperAssociation';
			JLoader::register($cName, JPath::clean(JPATH_COMPONENT_SITE . '/helpers/association.php'));

			if (class_exists($cName) && is_callable(array($cName, 'getAssociations')))
			{
				$cassociations = call_user_func(array($cName, 'getAssociations'));
			}

			// For each language...
			foreach ($languages as $i => &$language)
			{
				switch (true)
				{
					// Language without specific home menu
					case (!isset($this->home_pages[$i])):
						unset($languages[$i]);
						break;

					// Home page
					case ($is_home):
						$language->link = JRoute::_('index.php?lang=' . $language->sef . '&Itemid=' . $this->home_pages[$i]->id);
						break;

					// Current language link
					case ($i == $this->current_lang):
						$language->link = JUri::getInstance()->toString(array('path', 'query'));
						break;

					// Component association
					case (isset($cassociations[$i])):
						$language->link = JRoute::_($cassociations[$i] . '&lang=' . $language->sef);
						break;

					// Menu items association
					// Heads up! "$item = $menu" here below is an assignment, *NOT* comparison
					case (isset($associations[$i]) && ($item = $menu->getItem($associations[$i]))):
						$language->link = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $language->sef);
						break;

					// Too bad...
					default:
						unset($languages[$i]);
				}
			}

			// If there are at least 2 of them, add the rel="alternate" links to the <head>
			if (count($languages) > 1)
			{
				// Remove the sef from the default language if "Remove URL Language Code" is on
				if (isset($languages[$this->default_lang]) && $remove_default_prefix)
				{
					$languages[$this->default_lang]->link
									= preg_replace('|/' . $languages[$this->default_lang]->sef . '/|', '/', $languages[$this->default_lang]->link, 1);
				}

				foreach ($languages as $i => &$language)
				{
					$doc->addHeadLink($server . $language->link, 'alternate', 'rel', array('hreflang' => $i));
				}
			}
		}
	}

	/**
	 * Set the language cookie
	 *
	 * @param   string  $lang_code  The language code for which we want to set the cookie
	 *
	 * @return  void
	 *
	 * @since   3.4.2
	 */
	private function setLanguageCookie($lang_code)
	{
		// Get the cookie lifetime we want.
		$cookie_expire = 0;
		if ($this->params->get('lang_cookie', 1) == 1)
		{
			$cookie_expire = time() + 365 * 86400;
		}

		// Create a cookie.
		$cookie_domain = $this->app->get('cookie_domain');
		$cookie_path   = $this->app->get('cookie_path', '/');
		$cookie_secure = $this->app->isSSLConnection();
		$this->app->input->cookie->set(JApplicationHelper::getHash('language'), $lang_code, $cookie_expire, $cookie_path, $cookie_domain, $cookie_secure);
	}

	/**
	 * Get the client language from the cookie or the browser
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	private function getClientLanguage()
	{
		$cookie = $this->app->input->cookie->getString(JApplicationHelper::getHash('language'));
		$lang_code = $cookie;

		// If the cookie is not for a valid language code, fallback to null (so that in case we try the browser)
		if (!$this->checkLanguage($lang_code))
		{
			$lang_code = null;
		}

		// If we have an invalid cookie and the "Detect browser language" param is set, we try the browser language
		if (!$lang_code && $this->params->get('detect_browser', 0))
		{
			$lang_code = JLanguageHelper::detectLanguage();
		}

		// If we still have an invalid language we fallback to default
		if (!$this->checkLanguage($lang_code))
		{
			$lang_code = $this->default_lang;
		}

		// If the cookie was different (null or invalid) from the newly gotten language, we set a new cookie
		if ($lang_code != $cookie)
		{
			$this->setLanguageCookie($lang_code);
		}

		return $lang_code;
	}

	/**
	 * Check if the specified language code is valid (corresponding language is installed and has home page)
	 *
	 * @param   string  $lang_code  The language code to check
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function checkLanguage($lang_code)
	{
		return (isset($lang_code) && array_key_exists($lang_code, $this->lang_codes) && array_key_exists($lang_code, $this->home_pages));
	}

}
