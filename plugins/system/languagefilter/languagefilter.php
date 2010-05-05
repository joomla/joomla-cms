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
	public static $languages;
	public static $sefs;
	public static $site_sef;

	public function onAfterInitialise()
	{
		$app = JFactory::getApplication();
		if ($app->isSite()) {
			$app->setLanguageFilter(true);
			$router =& $app->getRouter();

			// attach build rules for language SEF
			$router->attachBuildRule(array($this, 'buildRule'));

			// attach parse rules for language SEF
			$router->attachParseRule(array($this, 'parseRule'));

			// load languages
			$db 	=& JFactory::getDBO();
			$query	= $db->getQuery(true);
			$query->select('*')->from('#__languages')->where('published=1');
			$db->setQuery($query);
			self::$sefs = $db->loadObjectList('sef');
			self::$languages = $db->loadObjectList('lang_code');
			self::$site_sef = self::$languages[JComponentHelper::getParams('com_languages')->get('site','en-GB')]->sef;
		}
	}

	public function buildRule(&$router, &$uri)
	{
		$sef = $uri->getVar('lang');
		if (empty($sef)) {
			$sef= self::$languages[JFactory::getLanguage()->getTag()]->sef;
		}
		elseif (!array_key_exists($sef, self::$sefs)) {
			$sef = self::$site_sef;
		}
		$Itemid = $uri->getVar('Itemid');
		if ($Itemid) {
			$menu =& JSite::getMenu()->getItem($Itemid);
			// if no menu - that means that we are routing home menu item of none-current language
			if (!$menu || $menu->home) {
				$uri->delVar('option');
				$uri->delVar('Itemid');
			}
		}
		if ($router->getMode() == JROUTER_MODE_SEF) {
			$uri->delVar('lang');
			$uri->setPath($uri->getPath().'/'.$sef.'/');
		}
		else {
			$uri->setVar('lang',$sef);
		}
	}

	public function parseRule(&$router, &$uri)
	{
		$array = array();
		if ($router->getMode() == JROUTER_MODE_SEF) {
			$path = $uri->getPath();
			$parts = explode('/', $path);

			$sef = $parts[0];

			if (!array_key_exists($sef,self::$sefs)) {
				$sef = self::$site_sef;
			}
			$lang_code=self::$sefs[$sef]->lang_code;

			if (!$lang_code ||  !JLanguage::exists($lang_code)) {
				// Use the default language
				$lang_code = self::$default_language;
			}
			else {
				array_shift($parts);
				$uri->setPath(implode('/', $parts));
			}

			$array = array('lang' => $sef);
			JFactory::getLanguage()->setLanguage($lang_code);
			$config = JFactory::getConfig();
			$cookie_domain 	= $config->get('config.cookie_domain', '');
			$cookie_path 	= $config->get('config.cookie_path', '/');
			setcookie(JUtility::getHash('language'), $lang_code, time() + 365 * 86400, $cookie_path, $cookie_domain);
		}
		return $array;
	}
}
