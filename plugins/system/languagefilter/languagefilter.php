<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
* Joomla! Language Filter Plugin
*
 * @package		Joomla
 * @subpackage	System
 * @since		1.6
 */
class plgSystemLanguageFilter extends JPlugin
{
	public static $mode_sef;
	public static $tag;
	public static $sefs;
	public static $lang_codes;

	public static $default_lang;
	public static $default_sef;

	public function __construct(&$subject, $config)
	{
		$app = JFactory::getApplication();
		if ($app->isSite()) {
			$app->setLanguageFilter(true);
			$router = $app->getRouter();

			// setup language data
			self::$mode_sef 	= ($router->getMode() == JROUTER_MODE_SEF) ? true : false;
			self::$sefs 		= JLanguageHelper::getLanguages('sef');
			self::$lang_codes 	= JLanguageHelper::getLanguages('lang_code');
			self::$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			self::$default_sef 	= self::$lang_codes[self::$default_lang]->sef;
			$uri = JFactory::getURI();
			if (self::$mode_sef) {
				$path = $uri->getPath();
				$path = substr($path,strpos($path,'index.php'));
				$parts = explode('/', $path);
				$sef = isset($parts[1]) ? $parts[1] : '';
			}
			else {
				$sef = $uri->getVar('lang');
			}
			if (isset(self::$sefs[$sef])) {
				$lang_code = self::$sefs[$sef]->lang_code;
				// Create a cookie
				$conf = JFactory::getConfig();
				$cookie_domain 	= $conf->get('config.cookie_domain', '');
				$cookie_path 	= $conf->get('config.cookie_path', '/');
				setcookie(JUtility::getHash('language'), $lang_code, time() + 365 * 86400, $cookie_path, $cookie_domain);
				// set the request var
				JRequest::setVar('language',$lang_code);
			}
		}
		parent::__construct($subject, $config);
	}

	public function onAfterInitialise()
	{
		$app = JFactory::getApplication();
		if ($app->isSite()) {
			self::$tag 			= JFactory::getLanguage()->getTag();

			$router = $app->getRouter();
			// attach build rules for language SEF
			$router->attachBuildRule(array($this, 'buildRule'));

			// attach parse rules for language SEF
			$router->attachParseRule(array($this, 'parseRule'));
		}
	}

	public function buildRule(&$router, &$uri)
	{
		$sef = $uri->getVar('lang');
		if (empty($sef)) {
			$sef = self::$lang_codes[self::$tag]->sef;
		}
		elseif (!isset(self::$sefs[$sef])) {
			$sef = self::$default_sef;
		}

		$Itemid = $uri->getVar('Itemid', 'absent');

		if ($Itemid != 'absent') {
			$app	= JFactory::getApplication();
			$menu 	= $app->getMenu()->getItem($Itemid);
			// if no menu - that means that we are routing home menu item of none-current language or alias to home
			if (!$menu || $menu->home && $uri->getVar('option')!='com_search') {
				$uri->delVar('option');
				$uri->delVar('Itemid');
			}
		}

		if (self::$mode_sef) {
			$uri->delVar('lang');
			$uri->setPath($uri->getPath().'/'.$sef.'/');
		}
		else {
			$uri->setVar('lang', $sef);
		}
	}

	public function parseRule(&$router, &$uri)
	{
		$array = array();
		if (self::$mode_sef) {
			$path = $uri->getPath();
			$parts = explode('/', $path);

			$sef = $parts[0];

			if (!isset(self::$sefs[$sef])) {
				$sef = self::$default_sef;
			}
			$lang_code = self::$sefs[$sef]->lang_code;
			if ($lang_code && JLanguage::exists($lang_code)) {
				array_shift($parts);
				$uri->setPath(implode('/', $parts));
			}
		}
		else {
			$sef = $uri->getVar('lang');
		}

		$array = array('lang' => $sef);
		return $array;
	}
}
