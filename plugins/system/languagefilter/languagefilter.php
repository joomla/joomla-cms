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

	protected $default_lang;

	private $user_lang_code;

	/**
	 * JDatabaseDriver instance
	 *
	 * @var    JDatabaseDriver
	 * @since  3.4.2
	 */
	protected $db = null;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.3
	 */
	protected $app;

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
			$this->mode_sef 	= $this->app->get('sef', 0);
			$this->sefs 		= JLanguageHelper::getLanguages('sef');
			$this->lang_codes 	= JLanguageHelper::getLanguages('lang_code');

			$levels = JFactory::getUser()->getAuthorisedViewLevels();

			foreach ($this->sefs as $sef => $language)
			{
				// @todo: In Joomla 2.5.4 and earlier access wasn't set. Non modified Content Languages got 0 as access value
				// we also check if frontend language exists and is enabled
				if (($language->access && !in_array($language->access, $levels))
					|| (!array_key_exists($language->lang_code, MultilangstatusHelper::getSitelangs())))
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
		if (isset($this->lang_codes[$this->default_lang]) && $this->lang_codes[$this->default_lang]->sitename)
		{
			$this->app->set('sitename', $this->lang_codes[$this->default_lang]->sitename);
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
		$lang = $uri->getVar('lang', $this->default_lang);
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
			$sef = $this->lang_codes[$this->default_lang]->sef;
		}

		if ($this->mode_sef
			&& (!$this->params->get('remove_default_prefix', 0)
			|| $lang != JComponentHelper::getParams('com_languages')->get('site', 'en-GB')
			|| $lang != $this->default_lang))
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
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function parseRule(&$router, &$uri)
	{
		// Did we find the current and existing language yet?
		$found = false;
		$lang_code = false;

		// Are we in SEF mode or not?
		if ($this->mode_sef)
		{
			$path = $uri->getPath();
			$parts = explode('/', $path);

			$sef = $parts[0];

			// If the default prefix should be removed and the SEF prefix is not among those
			// that we have in our system, its the default language and we "found" the right language
			if ($this->params->get('remove_default_prefix', 0) && !isset($this->sefs[$sef]))
			{
				if ($parts[0])
				{
					// We load a default site language page
					$lang_code = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				}
				else
				{
					// We check for an existing language cookie
					$lang_code = $this->app->input->cookie->getString(JApplicationHelper::getHash('language'));
				}

				if (!$lang_code && $this->params->get('detect_browser', 0) == 1)
				{
					$lang_code = JLanguageHelper::detectLanguage();
				}

				if (!$lang_code)
				{
					$lang_code = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				}

				if ($lang_code == JComponentHelper::getParams('com_languages')->get('site', 'en-GB'))
				{
					$found = true;
				}
			}
			else
			{
				// If the language prefix should always be present or it is indeed , we can now look it up in our array
				if (isset($this->sefs[$sef]))
				{
					// We found our language
					$found = true;
					$lang_code = $this->sefs[$sef]->lang_code;
				}

				// If we found our language, but its the default language and we don't want a prefix for that, we are on a wrong URL.
				// Or we try to change the language back to the default language. We need a redirect to the proper URL for the default language.
				if ($this->params->get('remove_default_prefix', 0)
					&& $lang_code == JComponentHelper::getParams('com_languages')->get('site', 'en-GB'))
				{
					// Create a cookie.
					$cookie_domain = $this->app->get('cookie_domain');
					$cookie_path   = $this->app->get('cookie_path', '/');
					$this->app->input->cookie->set(JApplicationHelper::getHash('language'), $lang_code, $this->getLangCookieTime(), $cookie_path, $cookie_domain);

					$found = false;
					array_shift($parts);
					$path = implode('/', $parts);
				}

				// We have found our language and the first part of our URL is the language prefix
				if ($found)
				{
					array_shift($parts);
					$uri->setPath(implode('/', $parts));
				}
			}
		}
		// We are not in SEF mode
		$lang = $uri->getVar('lang', $lang_code);

		if (isset($this->sefs[$lang]))
		{
			// We found our language
			$found = true;
			$lang_code = $this->sefs[$lang]->lang_code;
		}

		// We are called via POST. We don't care about the language
		// and simply set the default language as our current language.
		if ($this->app->input->getMethod() == "POST"
			|| count($this->app->input->post) > 0
			|| count($this->app->input->files) > 0)
		{
			$found = true;

			if (!isset($lang_code))
			{
				$lang_code = $this->app->input->cookie->getString(JApplicationHelper::getHash('language'));
			}

			if ($this->params->get('detect_browser', 1) && !$lang_code)
			{
				$lang_code = JLanguageHelper::detectLanguage();
			}

			if (!isset($this->lang_codes[$lang_code]))
			{
				$lang_code = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			}
		}

		// We have not found the language and thus need to redirect
		if (!$found)
		{
			// Lets find the default language for this user
			if (!isset($lang_code) || !isset($this->lang_codes[$lang_code]))
			{
				$lang_code = false;

				if ($this->params->get('detect_browser', 1))
				{
					$lang_code = JLanguageHelper::detectLanguage();
					if (!isset($this->lang_codes[$lang_code]))
					{
						$lang_code = false;
					}
				}

				if (!$lang_code)
				{
					$lang_code = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				}
				// Either we detected the language via the browser or we got it from the cookie. In worst case
				// we fall back to the application setting
				$lang_code = $this->app->input->cookie->getString(JApplicationHelper::getHash('language'), $lang_code);
			}

			if ($this->mode_sef)
			{
				// Use the current language sef or the default one.
				if (!$this->params->get('remove_default_prefix', 0)
					|| $lang_code != JComponentHelper::getParams('com_languages')->get('site', 'en-GB'))
				{
					$path = $this->lang_codes[$lang_code]->sef . '/' . $path;
				}
				$uri->setPath($path);

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
		$array = array('lang' => $lang_code);
		$this->default_lang = $lang_code;

		// Set the request var.
		$this->app->input->set('language', $lang_code);
		$this->app->set('language', $lang_code);
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
		if ($this->app->input->cookie->getString(JApplicationHelper::getHash('language')) != $lang_code)
		{
			$cookie_domain = $this->app->get('cookie_domain');
			$cookie_path   = $this->app->get('cookie_path', '/');
			$this->app->input->cookie->set(JApplicationHelper::getHash('language'), $lang_code, $this->getLangCookieTime(), $cookie_path, $cookie_domain);
		}

		return $array;
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
		if ($this->params->get('automatic_change', '1') == '1' && key_exists('params', $user))
		{
			$registry = new Registry;
			$registry->loadString($user['params']);
			$this->user_lang_code = $registry->get('language');

			if (empty($this->user_lang_code))
			{
				$this->user_lang_code = $this->default_lang;
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
		if ($this->params->get('automatic_change', '1') == '1' && key_exists('params', $user) && $success)
		{
			$registry = new Registry;
			$registry->loadString($user['params']);
			$lang_code = $registry->get('language');

			if (empty($lang_code))
			{
				$lang_code = $this->default_lang;
			}

			if ($lang_code == $this->user_lang_code || !isset($this->lang_codes[$lang_code]))
			{
				if ($this->app->isSite())
				{
					$this->app->setUserState('com_users.edit.profile.redirect', null);
				}
			}
			else
			{
				if ($this->app->isSite())
				{
					$this->app->setUserState('com_users.edit.profile.redirect', 'index.php?Itemid='
						. $this->app->getMenu()->getDefault($lang_code)->id . '&lang=' . $this->lang_codes[$lang_code]->sef
					);

					// Create a cookie.
					$cookie_domain 	= $this->app->get('cookie_domain', '');
					$cookie_path 	= $this->app->get('cookie_path', '/');
					setcookie(JApplicationHelper::getHash('language'), $lang_code, $this->getLangCookieTime(), $cookie_path, $cookie_domain);
				}
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
			// Load associations.
			$assoc = JLanguageAssociations::isEnabled();

			if ($assoc)
			{
				$active = $menu->getActive();

				if ($active)
				{
					$associations = MenusHelper::getAssociations($active->id);
				}
			}

			$lang_code = $user['language'];

			if (empty($lang_code))
			{
				$lang_code = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			}

			// Get a 1-dimensional array of published language codes
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('a.lang_code'))
				->from($this->db->quoteName('#__languages', 'a'))
				->where($this->db->quoteName('published') . ' = 1');
			$this->db->setQuery($query);
			$lang_codes = $this->db->loadColumn();

			// The user language has been deleted/disabled or the related content language does not exist/has been unpublished
			// or the related home page does not exist/has been unpublished
			if (!array_key_exists($lang_code, MultilangstatusHelper::getSitelangs())
				|| !in_array($lang_code, $lang_codes)
				|| !array_key_exists($lang_code, MultilangstatusHelper::getHomepages()))
			{
				$lang_code = $this->default_lang;
			}

			if ($lang_code != $this->default_lang)
			{
				// Change language.
				$this->default_lang = $lang_code;

				// Create a cookie.
				$cookie_domain	= $this->app->get('cookie_domain', '');
				$cookie_path	= $this->app->get('cookie_path', '/');
				setcookie(JApplicationHelper::getHash('language'), $lang_code, $this->getLangCookieTime(), $cookie_path, $cookie_domain);

				// Change the language code.
				JFactory::getLanguage()->setLanguage($lang_code);

				// Change the redirect (language has changed).
				if (isset($associations[$lang_code]) && $menu->getItem($associations[$lang_code]))
				{
					$itemid = $associations[$lang_code];
					$this->app->setUserState('users.login.form.return', 'index.php?&Itemid=' . $itemid);
				}
				else
				{
					$homes  = MultilangstatusHelper::getHomepages();
					$itemid = isset($homes[$lang_code]) ? $homes[$lang_code]->id : $homes['*']->id;
					$this->app->setUserState('users.login.form.return', 'index.php?&Itemid=' . $itemid);
				}
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

		if ($this->app->isSite() && JLanguageMultilang::isEnabled() && $this->params->get('alternate_meta') && $doc->getType() == 'html')
		{

			$languages = JLanguageHelper::getLanguages();
			$menu = $this->app->getMenu();
			$active = $menu->getActive();
			$lang = JFactory::getLanguage();
			$levels = JFactory::getUser()->getAuthorisedViewLevels();
			$homes = MultilangstatusHelper::getHomepages();
			$default_language_sef = $this->lang_codes[JComponentHelper::getParams('com_languages')->get('site', 'en-GB')]->sef;
			$remove_default_prefix = $this->params->get('remove_default_prefix', 0);

			// Load menu associations
			$home = false;
			if ($active)
			{
				$active_link = JRoute::_($active->link . '&Itemid=' . $active->id, false);
				$current_link = JUri::getInstance()->toString(array('path', 'query'));

				// If the menu item is a home menu item and the URLs are identical, we are on the homepage
				if ($active_link == $current_link  || $active_link == $current_link . 'index.php')
				{
					$home = true;
				}

				$associations = MenusHelper::getAssociations($active->id);
			}

			// Load component associations.
			$option = $this->app->input->get('option');
			$eName = JString::ucfirst(JString::str_ireplace('com_', '', $option));
			$cName = JString::ucfirst($eName . 'HelperAssociation');
			JLoader::register($cName, JPath::clean(JPATH_COMPONENT_SITE . '/helpers/association.php'));
			if (class_exists($cName) && is_callable(array($cName, 'getAssociations')))
			{
				$cassociations = call_user_func(array($cName, 'getAssociations'));
			}

			// We must count how many association we actually have
			$alternate_rels = 0;

			// For each language...
			foreach ($languages as $i => &$language)
			{
				$language->active = ($language->lang_code == $lang->getTag());

				// Do not display language without frontend UI
				if (!array_key_exists($language->lang_code, MultilangstatusHelper::getSitelangs()))
				{
					unset($languages[$i]);
				}
				// Do not display language without specific home menu
				elseif (!isset($homes[$language->lang_code]))
				{
					unset($languages[$i]);
				}
				// Do not display language without authorized access level
				elseif (isset($language->access) && $language->access && !in_array($language->access, $levels))
				{
					unset($languages[$i]);
				}
				// Component association
				elseif (isset($cassociations[$language->lang_code]))
				{
					$language->link = JRoute::_($cassociations[$language->lang_code] . '&lang=' . $language->sef);
					$alternate_rels++;
				}
				// Menu items association
				elseif (isset($associations[$language->lang_code]) && $menu->getItem($associations[$language->lang_code]))
				{
					$language->link = JRoute::_('index.php?lang=' . $language->sef . '&Itemid=' . $associations[$language->lang_code]);
					$alternate_rels++;
				}
				// I think we'll never fall here if we have associations for at least 2 languages, but...
				elseif ($language->active)
				{
					$language->link = JUri::getInstance()->toString(array('', '', 'port', 'path', 'query'));
					$alternate_rels++;
				}
				// Home page
				elseif ($home)
				{
					$language->link = JRoute::_('index.php?lang=' . $language->sef . '&Itemid=' . $homes[$language->lang_code]->id);
					$alternate_rels++;
				}
				// Too bad...
				else
				{
					unset($languages[$i]);
				}
			}

			// If there are at least 2 of them, add the rel="alternate" links to the <head>
			if ($alternate_rels > 1)
			{
				foreach ($languages as $i => &$language)
				{
					// Remove the sef if this is the default language and "Remove URL Language Code" is on
					if ($language->sef == $default_language_sef && $remove_default_prefix)
					{
						$language->link = preg_replace('|/' . $language->sef . '/|', '/', $language->link, 1);
					}
					$doc->addHeadLink($language->link, 'alternate', 'rel', array('hreflang' => $language->lang_code));
				}
			}
		}
	}

	/**
	 * Get the language cookie settings.
	 *
	 * @return  string  The cookie time.
	 *
	 * @since   3.0.4
	 */
	private function getLangCookieTime()
	{
		if ($this->params->get('lang_cookie', 1) == 1)
		{
			$lang_cookie = time() + 365 * 86400;
		}
		else
		{
			$lang_cookie = 0;
		}

		return $lang_cookie;
	}
}
