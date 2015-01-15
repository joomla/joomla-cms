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

	protected $tag;

	protected $sefs;

	protected $lang_codes;

	protected $homes;

	protected $default_lang;

	protected $default_sef;

	protected $cookie;

	private $user_lang_code;

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

		$this->cookie = SID == '';

		$router = $this->app->getRouter();

		if ($this->app->isSite())
		{
			// Setup language data.
			$this->mode_sef = ($router->getMode() == JROUTER_MODE_SEF) ? true : false;
			$this->sefs = JLanguageHelper::getLanguages('sef');
			$this->lang_codes = JLanguageHelper::getLanguages('lang_code');
			$this->default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			$this->default_sef = $this->lang_codes[$this->default_lang]->sef;
			$this->homes = MultilangstatusHelper::getHomepages();

			$user = JFactory::getUser();
			$levels = $user->getAuthorisedViewLevels();

			foreach ($this->sefs as $sef => &$language)
			{
				if (isset($language->access) && $language->access && !in_array($language->access, $levels))
				{
					unset($this->sefs[$sef]);
				}
			}

			$this->app->setLanguageFilter(true);

			$uri = JUri::getInstance();

			$sef = $uri->getVar('lang');

			if (!$sef)
			{
				// Get the route path from the request.
				$path = JString::substr($uri->toString(), JString::strlen($uri->base()));

				// Apache mod_rewrite is Off.
				$path = $this->app->get('sef_rewrite') ? $path : JString::substr($path, 10);

				// Trim any spaces or slashes from the ends of the path and explode into segments.
				$path = JString::trim($path, '/ ');
				$parts = explode('/', $path);

				if (!empty($parts) && empty($sef))
				{
					$sef = reset($parts);
				}
			}

			if (isset($this->sefs[$sef]))
			{
				$lang_code = $this->sefs[$sef]->lang_code;

				// Create a cookie.
				$cookie_domain = $this->app->get('cookie_domain', '');
				$cookie_path = $this->app->get('cookie_path', '/');
				setcookie(JApplicationHelper::getHash('language'), $lang_code, $this->getLangCookieTime(), $cookie_path, $cookie_domain);
				$this->app->input->cookie->set(JApplicationHelper::getHash('language'), $lang_code);

				// Set the request var.
				$this->app->input->set('language', $lang_code);
			}

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
			$this->tag = JFactory::getLanguage()->getTag();

			$router = $this->app->getRouter();

			// Attach build rules for language SEF.
			$router->attachBuildRule(array($this, 'buildRule'));

			// Attach parse rules for language SEF.
			$router->attachParseRule(array($this, 'parseRule'));

			// Add custom site name.
			if (isset($this->lang_codes[$this->tag]) && $this->lang_codes[$this->tag]->sitename)
			{
				$this->app->set('sitename', $this->lang_codes[$this->tag]->sitename);
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
			$sef = $this->lang_codes[$this->tag]->sef;
		}
		elseif (!isset($this->sefs[$sef]))
		{
			$sef = $this->default_sef;
		}

		$Itemid = $uri->getVar('Itemid');

		if (!is_null($Itemid))
		{
			if ($item = $this->app->getMenu()->getItem($Itemid))
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

		if ($this->mode_sef)
		{
			$uri->delVar('lang');

			if ($this->params->get('remove_default_prefix', 0) == 0
				|| $sef != $this->default_sef
				|| $sef != $this->lang_codes[$this->tag]->sef
				|| $this->params->get('detect_browser', 1) && JLanguageHelper::detectLanguage() != $this->tag && !$this->cookie)
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
		$lang_code = $this->app->input->cookie->getString(JApplicationHelper::getHash('language'));

		// No cookie - let's try to detect browser language or use site default.
		if (!$lang_code)
		{
			if ($this->params->get('detect_browser', 1))
			{
				$lang_code = JLanguageHelper::detectLanguage();
			}
			else
			{
				$lang_code = $this->default_lang;
			}
		}

		if ($this->mode_sef)
		{
			$path = $uri->getPath();
			$parts = explode('/', $path);

			$sef = $parts[0];

			// Redirect only if not in post.
			if (!empty($lang_code) && ($this->app->input->getMethod() != "POST"
				|| (count($this->app->input->post) == 0 && count($this->app->input->files) == 0)))
			{
				if ($this->params->get('remove_default_prefix', 0) == 0)
				{
					// Redirect if sef does not exist.
					if (!isset($this->sefs[$sef]))
					{
						// Use the current language sef or the default one.
						$sef = isset($this->lang_codes[$lang_code]) ? $this->lang_codes[$lang_code]->sef : $this->default_sef;
						$uri->setPath($sef . '/' . $path);

						if ($this->app->get('sef_rewrite'))
						{
							$this->app->redirect($uri->base() . $uri->toString(array('path', 'query', 'fragment')));
						}
						else
						{
							$path = $uri->toString(array('path', 'query', 'fragment'));
							$this->app->redirect($uri->base() . 'index.php' . ($path ? ('/' . $path) : ''));
						}
					}
				}
				else
				{
					// Redirect if sef does not exist and language is not the default one.
					if (!isset($this->sefs[$sef]) && $lang_code != $this->default_lang)
					{
						$sef = isset($this->lang_codes[$lang_code]) && empty($path) ? $this->lang_codes[$lang_code]->sef : $this->default_sef;
						$uri->setPath($sef . '/' . $path);

						if ($this->app->get('sef_rewrite'))
						{
							$this->app->redirect($uri->base() . $uri->toString(array('path', 'query', 'fragment')));
						}
						else
						{
							$path = $uri->toString(array('path', 'query', 'fragment'));
							$this->app->redirect($uri->base() . 'index.php' . ($path ? ('/' . $path) : ''));
						}
					}
					// Redirect if sef is the default one.
					elseif (isset($this->sefs[$sef]) &&
						$this->default_lang == $this->sefs[$sef]->lang_code &&
						(!$this->params->get('detect_browser', 1) || JLanguageHelper::detectLanguage() == $this->tag || $this->cookie)
					)
					{
						array_shift($parts);
						$uri->setPath(implode('/', $parts));

						if ($this->app->get('sef_rewrite'))
						{
							$this->app->redirect($uri->base() . $uri->toString(array('path', 'query', 'fragment')), true);
						}
						else
						{
							$path = $uri->toString(array('path', 'query', 'fragment'));
							$this->app->redirect($uri->base() . 'index.php' . ($path ? ('/' . $path) : ''), true);
						}
					}
				}
			}

			$lang_code = isset($this->sefs[$sef]) ? $this->sefs[$sef]->lang_code : '';

			if ($lang_code && JLanguage::exists($lang_code))
			{
				array_shift($parts);
				$uri->setPath(implode('/', $parts));
			}
		}
		else
		{
			$sef = $uri->getVar('lang');

			if (!isset($this->sefs[$sef]))
			{
				$sef = isset($this->lang_codes[$lang_code]) ? $this->lang_codes[$lang_code]->sef : $this->default_sef;
				$uri->setVar('lang', $sef);

				if ($this->app->input->getMethod() != "POST" || count($this->app->input->post) == 0)
				{
					$this->app->redirect(JUri::base(true) . '/index.php?' . $uri->getQuery());
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
					$this->tag = $lang_code;

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
				$lang_code = $this->default_lang;
			}

			if ($lang_code != $this->tag)
			{
				// Change language.
				$this->tag = $lang_code;

				// Create a cookie.
				$cookie_domain 	= $this->app->get('cookie_domain', '');
				$cookie_path 	= $this->app->get('cookie_path', '/');
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
					$itemid = isset($this->homes[$lang_code]) ? $this->homes[$lang_code]->id : $this->homes['*']->id;
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
		$menu = $this->app->getMenu();
		$server = JUri::getInstance()->toString(array('scheme', 'host', 'port'));
		$option = $this->app->input->get('option');
		$eName = JString::ucfirst(JString::str_ireplace('com_', '', $option));

		if ($this->app->isSite() && $this->params->get('alternate_meta') && $doc->getType() == 'html')
		{
			// Get active menu item.
			$active = $menu->getActive();

			$assocs = array();

			// Load menu associations.
			if ($active)
			{
				$active_link = JRoute::_($active->link . '&Itemid=' . $active->id, false);

				// Get current link.
				$current_link = JUri::getInstance()->toString(array('path', 'query'));

				// Check the exact menu item's URL.
				if ($active_link == $current_link)
				{
					$associations = MenusHelper::getAssociations($active->id);
					unset($associations[$active->language]);
					$assocs = array_keys($associations);
				}
			}

			// Load component associations.
			$cName = JString::ucfirst($eName . 'HelperAssociation');
			JLoader::register($cName, JPath::clean(JPATH_COMPONENT_SITE . '/helpers/association.php'));

			if (class_exists($cName) && is_callable(array($cName, 'getAssociations')))
			{
				$cassociations = call_user_func(array($cName, 'getAssociations'));

				$lang_code = $this->app->input->cookie->getString(JApplicationHelper::getHash('language'));

				// No cookie - let's try to detect browser language or use site default.
				if (!$lang_code)
				{
					if ($this->params->get('detect_browser', 1))
					{
						$lang_code = JLanguageHelper::detectLanguage();
					}
					else
					{
						$lang_code = $this->default_lang;
					}
				}

				unset($cassociations[$lang_code]);
				$assocs = array_merge(array_keys($cassociations), $assocs);
			}

			// Handle the default associations.
			if ($this->params->get('item_associations'))
			{
				$languages = JLanguageHelper::getLanguages('lang_code');
				foreach ($assocs as $language)
				{
					if (!JLanguage::exists($language))
					{
						continue;
					}
					$lang = $languages[$language];

					if (isset($cassociations[$language]))
					{
						$link = JRoute::_($cassociations[$language] . '&lang=' . $lang->sef);

						// Check if language is the default site language and remove url language code is on
						if ($lang->sef == $this->default_sef && $this->params->get('remove_default_prefix') == '1')
						{
							$link = preg_replace('|/' . $lang->sef . '/|', '/', $link, 1);
						}

						$doc->addHeadLink($server . $link, 'alternate', 'rel', array('hreflang' => $language));
					}
					elseif (isset($associations[$language]))
					{
						$item = $menu->getItem($associations[$language]);

						if ($item)
						{
							$link = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $lang->sef);

							// Check if language is the default site language and remove url language code is on
							if ($lang->sef == $this->default_sef && $this->params->get('remove_default_prefix') == '1')
							{
								$link = preg_replace('|/' . $lang->sef . '/|', '/', $link, 1);
							}

							$doc->addHeadLink($server . $link, 'alternate', 'rel', array('hreflang' => $language));
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
						$link = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $language->sef);

						// Check if language is the default site language and remove url language code is on
						if ($language->sef == $this->default_sef && $this->params->get('remove_default_prefix') == '1')
						{
							$link = preg_replace('|/' . $language->sef . '/|', '/', $link, 1);
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
