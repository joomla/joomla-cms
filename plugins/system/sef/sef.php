<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.sef
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Joomla! SEF Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.sef
 * @since       1.5
 */
class PlgSystemSef extends JPlugin
{
	/**
	 * Add the canonical uri to the head.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		if ($app->getName() != 'site' || $doc->getType() !== 'html')
		{
			return true;
		}

		$router = $app->getRouter();

		$uri     = clone JUri::getInstance();
		$domain  = $this->params->get('domain');

		if ($domain === null || $domain === '')
		{
			$domain = $uri->toString(array('scheme', 'host', 'port'));
		}

		$parsed = $router->parse($uri);
		$fakelink = 'index.php?' . http_build_query($parsed);
		$link = $domain . JRoute::_($fakelink, false);

		if ($uri !== $link)
		{
			$doc->addHeadLink(htmlspecialchars($link), 'canonical');
		}
	}

	/**
	 * Convert the site URL to fit to the HTTP request.
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		if ($app->getName() != 'site' || $app->getCfg('sef') == '0')
		{
			return true;
		}

		// Replace src links.
		$base   = JUri::base(true).'/';
		$buffer = $app->getBody();

		$regex  = '#href="index.php\?([^"]*)#m';
		$buffer = preg_replace_callback($regex, array('PlgSystemSef', 'route'), $buffer);
		$this->checkBuffer($buffer);

		// Check for all unknown protocals (a protocol must contain at least one alpahnumeric character followed by a ":").
		$protocols = '[a-zA-Z0-9]+:';
		$regex     = '#(src|href|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$buffer    = preg_replace($regex, "$1=\"$base\$2\"", $buffer);
		$this->checkBuffer($buffer);

		$regex  = '#(onclick="window.open\(\')(?!/|' . $protocols . '|\#)([^/]+[^\']*?\')#m';
		$buffer = preg_replace($regex, '$1' . $base . '$2', $buffer);
		$this->checkBuffer($buffer);

		// ONMOUSEOVER / ONMOUSEOUT
		$regex  = '#(onmouseover|onmouseout)="this.src=([\']+)(?!/|' . $protocols . '|\#|\')([^"]+)"#m';
		$buffer = preg_replace($regex, '$1="this.src=$2' . $base .'$3$4"', $buffer);
		$this->checkBuffer($buffer);

		// Background image.
		$regex  = '#style\s*=\s*[\'\"](.*):\s*url\s*\([\'\"]?(?!/|' . $protocols . '|\#)([^\)\'\"]+)[\'\"]?\)#m';
		$buffer = preg_replace($regex, 'style="$1: url(\'' . $base .'$2$3\')', $buffer);
		$this->checkBuffer($buffer);

		// OBJECT <param name="xx", value="yy"> -- fix it only inside the <param> tag.
		$regex  = '#(<param\s+)name\s*=\s*"(movie|src|url)"[^>]\s*value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$buffer = preg_replace($regex, '$1name="$2" value="' . $base . '$3"', $buffer);
		$this->checkBuffer($buffer);

		// OBJECT <param value="xx", name="yy"> -- fix it only inside the <param> tag.
		$regex  = '#(<param\s+[^>]*)value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"\s*name\s*=\s*"(movie|src|url)"#m';
		$buffer = preg_replace($regex, '<param value="' . $base .'$2" name="$3"', $buffer);
		$this->checkBuffer($buffer);

		// OBJECT data="xx" attribute -- fix it only in the object tag.
		$regex  = '#(<object\s+[^>]*)data\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$buffer = preg_replace($regex, '$1data="' . $base . '$2"$3', $buffer);
		$this->checkBuffer($buffer);

		$app->setBody($buffer);
		return true;
	}

	/**
	 * Check the buffer.
	 *
	 * @param   string  $buffer  Buffer to be checked.
	 *
	 * @return  void
	 */
	private function checkBuffer($buffer)
	{
		if ($buffer === null)
		{
			switch (preg_last_error())
			{
				case PREG_BACKTRACK_LIMIT_ERROR:
					$message = "PHP regular expression limit reached (pcre.backtrack_limit)";
					break;
				case PREG_RECURSION_LIMIT_ERROR:
					$message = "PHP regular expression limit reached (pcre.recursion_limit)";
					break;
				case PREG_BAD_UTF8_ERROR:
					$message = "Bad UTF8 passed to PCRE function";
					break;
				default:
					$message = "Unknown PCRE error calling PCRE function";
			}
			throw new RuntimeException($message);
		}
	}

	/**
	 * Replace the matched tags.
	 *
	 * @param   array  &$matches  An array of matches (see preg_match_all).
	 *
	 * @return  string
	 */
	protected static function route(&$matches)
	{
		$url   = $matches[1];
		$url   = str_replace('&amp;', '&', $url);
		$route = JRoute::_('index.php?'.$url);

		return 'href="' . $route;
	}
}
