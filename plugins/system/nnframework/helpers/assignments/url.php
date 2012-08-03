<?php
/**
 * NoNumber Framework Helper File: Assignments: URL
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Assignments: URL
 */
class NNFrameworkAssignmentsURL
{
	var $_version = '12.6.4';

	/**
	 * passURL
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passURL(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		$url = JFactory::getURI();
		$url = $url->toString();

		if (!is_array($selection)) {
			$selection = explode("\n", $selection);
		}

		$pass = 0;
		foreach ($selection as $url_part) {
			if ($url_part !== '') {
				$url_part = trim(str_replace('&amp;', '(&amp;|&)', $url_part));
				$s = '#'.$url_part.'#si';
				if (@preg_match($s.'u', $url)
					|| @preg_match($s.'u', html_entity_decode($url, ENT_COMPAT, 'UTF-8'))
						|| @preg_match($s, $url)
							|| @preg_match($s, html_entity_decode($url, ENT_COMPAT, 'UTF-8'))
				) {
					$pass = 1;
					break;
				}
			}
		}

		if ($pass) {
			return ($assignment == 'include');
		} else {
			return ($assignment == 'exclude');
		}
	}

	/**
	 * passHomePage
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passHomePage(&$main, &$params, $selection = array(), $assignment = 'all', $addlang = 0)
	{
		$app = JFactory::getApplication();

		$pass = 0;

		$uri = JFactory::getURI();

		if ($addlang) {
			$sef = $uri->getVar('lang');
			if (empty($sef)) {
				$langs = array_keys(JLanguageHelper::getLanguages('sef'));
				$path = JString::substr($uri->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path')), JString::strlen($uri->base()));
				$path = preg_replace('#^index\.php/?#', '', $path);
				$parts = explode('/', $path);
				$part = reset($parts);
				if (in_array($part, $langs)) {
					$sef = $part;
				}
			}
			if (empty($sef)) {
				return 0;
			}
		}

		$query = $uri->toString(array('query'));
		if (strpos($query, 'option=') === false && strpos($query, 'Itemid=') === false) {
			$url = $uri->toString(array('host', 'path'));
		} else {
			$url = $uri->toString(array('host', 'path', 'query'));
		}

		// remove the www.
		$url = preg_replace('#^www\.#', '', $url);
		// replace ampersand chars
		$url = str_replace('&amp;', '&', $url);
		// remove any language vars
		$url = preg_replace('#((\?)lang=[a-z-_]*(&|$)|&lang=[a-z-_]*)#', '\2', $url);
		// remove trailing nonsense
		$url = trim(preg_replace('#/?\??&?$#', '', $url));
		// remove the index.php/
		$url = preg_replace('#/index\.php(/|$)#', '/', $url);
		// remove trailing /
		$url = trim(preg_replace('#/$#', '', $url));

		$root = JURI::root();

		// remove the http(s)
		$root = preg_replace('#^.*?://#', '', $root);
		// remove the www.
		$root = preg_replace('#^www\.#', '', $root);
		// so also passes on urls with trailing /, ?, &, /?, etc...
		$root = preg_replace('#(Itemid=[0-9]*).*^#', '\1', $root);
		// remove trailing /
		$root = trim(preg_replace('#/$#', '', $root));

		if ($addlang) {
			$root .= '/'.$sef;
		}

		if (!$pass) {
			/* Pass urls:
			 * [root]
			 */
			$regex = '#^'.$root.'$#i';
			$pass = preg_match($regex, $url);
		}

		if (!$pass) {
			$menu = $app->getMenu('site');
			$menu_def = $menu->getDefault();
			/* Pass urls:
			 * [root]?Itemid=[menu-id]
			 * [root]/?Itemid=[menu-id]
			 * [root]/index.php?Itemid=[menu-id]
			 * [root]/[menu-alias]
			 * [root]/[menu-alias]?Itemid=[menu-id]
			 * [root]/index.php?[menu-alias]
			 * [root]/index.php?[menu-alias]?Itemid=[menu-id]
			 * [root]/[menu-link]
			 * [root]/[menu-link]&Itemid=[menu-id]
			 */
			$regex = '#^'.$root
				.'(/('
				.'index\.php'
				.'|'
				.'(index\.php\?)?'.preg_quote($menu_def->alias, '#')
				.'|'
				.preg_quote($menu_def->link, '#')
				.')?)?'
				.'(/?[\?&]Itemid='.(int) $menu_def->id.')?'
				.'$#i';
			$pass = preg_match($regex, $url);
		}

		if (!$pass && !$addlang) {
			$pass = $this->passHomePage($main, $params, $selection, $assignment, 1);
		}

		if ($pass) {
			return ($assignment == 'include');
		} else {
			return ($assignment == 'exclude');
		}
	}
}