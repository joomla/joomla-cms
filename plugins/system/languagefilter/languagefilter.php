<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagefilter
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

JLoader::register('MultilangstatusHelper', JPATH_ADMINISTRATOR . '/components/com_languages/helpers/multilangstatus.php');

/**
 * Joomla! Language Filter Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.languagefilter
 * @since       1.6
 */
class PlgSystemLanguageFilter extends JPlugin
{
	protected static $mode_sef;

	protected static $tag;

	protected static $sefs;

	protected static $lang_codes;

	protected static $homes;

	protected static $default_lang;

	protected static $default_sef;

	protected static $cookie;

	private static $user_lang_code;

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

		// Ensure that constructor is called one time.
		self::$cookie = SID == '';

		if (!self::$default_lang)
		{
			$app = JFactory::getApplication();
			$router = $app::getRouter();

			if ($app->isSite())
			{
				// Setup language data.
				self::$mode_sef 	= ($router->getMode() == JROUTER_MODE_SEF) ? true : false;
				self::$sefs 		= JLanguageHelper::getLanguages('sef');
				self::$lang_codes 	= JLanguageHelper::getLanguages('lang_code');
				self::$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				self::$default_sef 	= self::$lang_codes[self::$default_lang]->sef;
				self::$homes		= MultilangstatusHelper::getHomes();

				$user = JFactory::getUser();
				$levels = $user->getAuthorisedViewLevels();

				foreach (self::$sefs as $sef => &$language)
				{
					if (isset($language->access) && $language->access && !in_array($language->access, $levels))
					{
						unset(self::$sefs[$sef]);
					}
				}

				$app->setLanguageFilter(true);
				jimport('joomla.environment.uri');
				$uri = JUri::getInstance();

				if (self::$mode_sef)
				{
					// Get the route path from the request.
					$path = JString::substr($uri->toString(), JString::strlen($uri->base()));

					// Apache mod_rewrite is Off.
					$path = JFactory::getConfig()->get('sef_rewrite') ? $path : JString::substr($path, 10);

					// Trim any spaces or slashes from the ends of the path and explode into segments.
					$path  = JString::trim($path, '/ ');
					$parts = explode('/', $path);

					// The language segment is always at the beginning of the route path if it exists.
					$sef = $uri->getVar('lang');

					if (!empty($parts) && empty($sef))
					{
						$sef = reset($parts);
					}
				}
				else
				{
					$sef = $uri->getVar('lang');
				}

				if (isset(self::$sefs[$sef]))
				{
					$lang_code = self::$sefs[$sef]->lang_code;

					// Create a cookie.
					$conf = JFactory::getConfig();
					$cookie_domain 	= $conf->get('cookie_domain', '');
					$cookie_path 	= $conf->get('cookie_path', '/');
					setcookie(JApplication::getHash('language'), $lang_code, $this->getLangCookieTime(), $cookie_path, $cookie_domain);
					$app->input->cookie->set(JApplication::getHash('language'), $lang_code);

					// Set the request var.
					$app->input->set('language', $lang_code);
				}
			}

			parent::__construct($subject, $config);

			// Detect browser feature.
			if ($app->isSite())
			{
				$app->setDetectBrowser($this->params->get('detect_browser', '1') == '1');
			}
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
		$app = JFactory::getApplication();
		$app->item_associations = $this->params->get('item_associations', 0);

		if ($app->isSite())
		{
			self::$tag = JFactory::getLanguage()->getTag();

			$router = $app::getRouter();

			// Attach build rules for language SEF.
			$router->attachBuildRule(array($this, 'buildRule'));

			// Attach parse rules for language SEF.
			$router->attachParseRule(array($this, 'parseRule'));

			// Add custom site name.
			$languages = JLanguageHelper::getLanguages('lang_code');

			if (isset($languages[self::$tag]) && $languages[self::$tag]->sitename)
			{
				JFactory::getConfig()->set('sitename', $languages[self::$tag]->sitename);
			}
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
		$sef = $uri->getVar('lang');

		if (empty($sef))
		{
			$sef = self::$lang_codes[self::$tag]->sef;
		}
		elseif (!isset(self::$sefs[$sef]))
		{
			$sef = self::$default_sef;
		}

		$Itemid = $uri->getVar('Itemid');

		if (!is_null($Itemid))
		{
			if ($item = JFactory::getApplication()->getMenu()->getItem($Itemid))
			{
				if ($item->home && $uri->getVar('option') != 'com_search')
				{
					$link = $item->link;
					$parts = JString::parse_url($link);

					if (isset ($parts['query']) && strpos($parts['query'], '&amp;'))
					{
						$parts['query'] = str_replace('&amp;', '&', $parts['query']);
					}

					parse_str($parts['query'], $vars);

					// Test if the url contains same vars as in menu link.
					$test = true;

					foreach ($uri->getQuery(true) as $key => $value)
					{
						if (!in_array($key, array('format', 'Itemid', 'lang')) && !(isset($vars[$key]) && $vars[$key] == $value))
						{
							$test = false;
							break;
						}
					}

					if ($test)
					{
						foreach ($vars as $key => $value)
						{
							$uri->delVar($key);
						}

						$uri->delVar('Itemid');
					}
				}
			}
			else
			{
				$uri->delVar('Itemid');
			}
		}

		if (self::$mode_sef)
		{
			$uri->delVar('lang');

			if ($this->params->get('remove_default_prefix', 0) == 0
				|| $sef != self::$default_sef
				|| $sef != self::$lang_codes[self::$tag]->sef
				|| $this->params->get('detect_browser', 1) && JLanguageHelper::detectLanguage() != self::$tag && !self::$cookie)
			{
				$uri->setPath($uri->getPath() . '/' . $sef . '/');
			}
			else
			{
				$uri->setPath($uri->getPath());
			}
		}
		else
		{
			$uri->setVar('lang', $sef);
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
		$app = JFactory::getApplication();

		$lang_code = $app->input->cookie->getString(JApplication::getHash('language'));

		// No cookie - let's try to detect browser language or use site default.
		if (!$lang_code)
		{
			if ($this->params->get('detect_browser', 1))
			{
				$lang_code = JLanguageHelper::detectLanguage();
			}
			else
			{
				$lang_code = self::$default_lang;
			}
		}

		if (self::$mode_sef)
		{
			$path = $uri->getPath();
			$parts = explode('/', $path);

			$sef = $parts[0];

			// Redirect only if not in post.
			if (!empty($lang_code) && ($app->input->getMethod() != "POST" || (count($app->input->post) == 0 && count($app->input->files) == 0)))
			{
				if ($this->params->get('remove_default_prefix', 0) == 0)
				{
					// Redirect if sef does not exist.
					if (!isset(self::$sefs[$sef]))
					{
						// Use the current language sef or the default one.
						$sef = isset(self::$lang_codes[$lang_code]) ? self::$lang_codes[$lang_code]->sef : self::$default_sef;
						$uri->setPath($sef . '/' . $path);

						if ($app->get('sef_rewrite'))
						{
							$app->redirect($uri->base() . $uri->toString(array('path', 'query', 'fragment')));
						}
						else
						{
							$path = $uri->toString(array('path', 'query', 'fragment'));
							$app->redirect($uri->base() . 'index.php' . ($path ? ('/' . $path) : ''));
						}
					}
				}
				else
				{
					// Redirect if sef does not exist and language is not the default one.
					if (!isset(self::$sefs[$sef]) && $lang_code != self::$default_lang)
					{
						$sef = isset(self::$lang_codes[$lang_code]) && empty($path) ? self::$lang_codes[$lang_code]->sef : self::$default_sef;
						$uri->setPath($sef . '/' . $path);

						if ($app->get('sef_rewrite'))
						{
							$app->redirect($uri->base() . $uri->toString(array('path', 'query', 'fragment')));
						}
						else
						{
							$path = $uri->toString(array('path', 'query', 'fragment'));
							$app->redirect($uri->base() . 'index.php' . ($path ? ('/' . $path) : ''));
						}
					}
					// Redirect if sef is the default one.
					elseif (isset(self::$sefs[$sef]) &&
						self::$default_lang == self::$sefs[$sef]->lang_code &&
						(!$this->params->get('detect_browser', 1) || JLanguageHelper::detectLanguage() == self::$tag || self::$cookie)
					)
					{
						array_shift($parts);
						$uri->setPath(implode('/', $parts));

						if ($app->get('sef_rewrite'))
						{
							$app->redirect($uri->base() . $uri->toString(array('path', 'query', 'fragment')));
						}
						else
						{
							$path = $uri->toString(array('path', 'query', 'fragment'));
							$app->redirect($uri->base() . 'index.php' . ($path ? ('/' . $path) : ''));
						}
					}
				}
			}

			$lang_code = isset(self::$sefs[$sef]) ? self::$sefs[$sef]->lang_code : '';

			if ($lang_code && JLanguage::exists($lang_code))
			{
				array_shift($parts);
				$uri->setPath(implode('/', $parts));
			}
		}
		else
		{
			$sef = $uri->getVar('lang');

			if (!isset(self::$sefs[$sef]))
			{
				$sef = isset(self::$lang_codes[$lang_code]) ? self::$lang_codes[$lang_code]->sef : self::$default_sef;
				$uri->setVar('lang', $sef);

				if ($app->input->getMethod() != "POST" || count($app->input->post) == 0)
				{
					$app->redirect(JUri::base(true) . '/index.php?' . $uri->getQuery());
				}
			}
		}

		$array = array('lang' => $sef);

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
			$registry = new JRegistry;
			$registry->loadString($user['params']);
			self::$user_lang_code = $registry->get('language');

			if (empty(self::$user_lang_code))
			{
				self::$user_lang_code = self::$default_lang;
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
			$registry = new JRegistry;
			$registry->loadString($user['params']);
			$lang_code = $registry->get('language');

			if (empty($lang_code))
			{
				$lang_code = self::$default_lang;
			}

			$app = JFactory::getApplication();

			if ($lang_code == self::$user_lang_code || !isset(self::$lang_codes[$lang_code]))
			{
				if ($app->isSite())
				{
					$app->setUserState('com_users.edit.profile.redirect', null);
				}
			}
			else
			{
				if ($app->isSite())
				{
					$app->setUserState('com_users.edit.profile.redirect', 'index.php?Itemid='
						. $app->getMenu()->getDefault($lang_code)->id . '&lang=' . self::$lang_codes[$lang_code]->sef
					);
					self::$tag = $lang_code;

					// Create a cookie.
					$conf = JFactory::getConfig();
					$cookie_domain 	= $conf->get('cookie_domain', '');
					$cookie_path 	= $conf->get('cookie_path', '/');
					setcookie(JApplication::getHash('language'), $lang_code, $this->getLangCookieTime(), $cookie_path, $cookie_domain);
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
		$app  = JFactory::getApplication();
		$menu = $app->getMenu();

		if ($app->isSite() && $this->params->get('automatic_change', 1))
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
				$lang_code = self::$default_lang;
			}

			if ($lang_code != self::$tag)
			{
				// Change language.
				self::$tag = $lang_code;

				// Create a cookie.
				$conf = JFactory::getConfig();
				$cookie_domain 	= $conf->get('cookie_domain', '');
				$cookie_path 	= $conf->get('cookie_path', '/');
				setcookie(JApplication::getHash('language'), $lang_code, $this->getLangCookieTime(), $cookie_path, $cookie_domain);

				// Change the language code.
				JFactory::getLanguage()->setLanguage($lang_code);

				// Change the redirect (language has changed).
				if (isset($associations[$lang_code]) && $menu->getItem($associations[$lang_code]))
				{
					$itemid = $associations[$lang_code];
					$app->setUserState('users.login.form.return', 'index.php?&Itemid=' . $itemid);
				}
				else
				{
					$itemid = isset(self::$homes[$lang_code]) ? self::$homes[$lang_code]->id : self::$homes['*']->id;
					$app->setUserState('users.login.form.return', 'index.php?&Itemid=' . $itemid);
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
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$menu = $app->getMenu();
		$server = JUri::getInstance()->toString(array('scheme', 'host', 'port'));
		$option = $app->input->get('option');
		$eName = JString::ucfirst(JString::str_ireplace('com_', '', $option));

		if ($app->isSite() && $this->params->get('alternate_meta') && $doc->getType() == 'html')
		{
			// Get active menu item.
			$active = $menu->getActive();

			// Load menu associations.
			if ($active)
			{
				// Get menu item link.
				if ($app->get('sef'))
				{
					$active_link = JRoute::_('index.php?Itemid=' . $active->id, false);
				}
				else
				{
					$active_link = JRoute::_($active->link . '&Itemid=' . $active->id, false);
				}

				if ($active_link == JUri::base(true) . '/')
				{
					$active_link .= 'index.php';
				}

				// Get current link.
				$current_link = JUri::getInstance()->toString(array('path', 'query'));

				if ($current_link == JUri::base(true) . '/')
				{
					$current_link .= 'index.php';
				}

				// Check the exact menu item's URL.
				if ($active_link == $current_link)
				{
					$associations = MenusHelper::getAssociations($active->id);
					unset($associations[$active->language]);
				}
			}

			// Load component associations.
			$cName = JString::ucfirst($eName . 'HelperAssociation');
			JLoader::register($cName, JPath::clean(JPATH_COMPONENT_SITE . '/helpers/association.php'));

			if (class_exists($cName) && is_callable(array($cName, 'getAssociations')))
			{
				$cassociations = call_user_func(array($cName, 'getAssociations'));

				$lang_code = $app->input->cookie->getString(JApplication::getHash('language'));

				// No cookie - let's try to detect browser language or use site default.
				if (!$lang_code)
				{
					if ($this->params->get('detect_browser', 1))
					{
						$lang_code = JLanguageHelper::detectLanguage();
					}
					else
					{
						$lang_code = self::$default_lang;
					}
				}

				unset($cassociations[$lang_code]);
			}

			// Handle the default associations.
			if ((!empty($associations) || !empty($cassociations)) && $this->params->get('item_associations'))
			{
				foreach (JLanguageHelper::getLanguages() as $language)
				{
					if (!JLanguage::exists($language->lang_code))
					{
						continue;
					}

					if (isset($cassociations[$language->lang_code]))
					{
						$link = JRoute::_($cassociations[$language->lang_code] . '&lang=' . $language->sef);
						$doc->addHeadLink($server . $link, 'alternate', 'rel', array('hreflang' => $language->lang_code));
					}
					elseif (isset($associations[$language->lang_code]))
					{
						$item = $menu->getItem($associations[$language->lang_code]);

						if ($item)
						{
							if ($app->get('sef'))
							{
								$link = JRoute::_('index.php?Itemid=' . $item->id . '&lang=' . $language->sef);
							}
							else
							{
								$link = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $language->sef);
							}

							$doc->addHeadLink($server . $link, 'alternate', 'rel', array('hreflang' => $language->lang_code));
						}
					}
				}
			}
			// Link to the home page of each language.
			elseif ($active && $active->home)
			{
				foreach (JLanguageHelper::getLanguages() as $language)
				{
					if (!JLanguage::exists($language->lang_code))
					{
						continue;
					}

					$item = $menu->getDefault($language->lang_code);

					if ($item && $item->language != $active->language && $item->language != '*')
					{
						if ($app->get('sef'))
						{
							$link = JRoute::_('index.php?Itemid=' . $item->id . '&lang=' . $language->sef);
						}
						else
						{
							$link = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $language->sef);
						}

						$doc->addHeadLink($server . $link, 'alternate', 'rel', array('hreflang' => $language->lang_code));
					}
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
