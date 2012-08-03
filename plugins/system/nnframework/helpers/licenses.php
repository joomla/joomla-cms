<?php
/**
 * NoNumber Framework Helper File: Licenses
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

class NNLicenses
{
	public static $instance = null;

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new NoNumberLicenses;
		}

		return self::$instance;
	}

	public static function instance()
	{
		// backward compatibility
		return self::getInstance();
	}
}

class NoNumberLicenses
{
	var $_version = '12.6.4';

	function getMessage($name = '')
	{
		if (!$name || in_array($name, array('CUSTOMENU'))) {
			return '';
		}

		$alias = preg_replace('#[^a-z\-]#', '', strtolower($name));
		$type = $this->getVersionType($alias);

		if ($type == 'pro') {
			return '';
		}

		$text = html_entity_decode(JText::sprintf('NN_USING_FREE_VERSION', JText::_($name)), ENT_COMPAT, 'UTF-8');
		if (!(strpos($text, '. ') === false)) {
			$text = explode('. ', $text);
			$text = '<span style="white-space:nowrap;">'.implode('.</span> <span style="white-space:nowrap;">', $text).'</span>';
		}

		$html = array();

		$html[] = '<div style="border:1px solid #CCCCCC;background-color:#FFCCCC;margin:10px 0;padding: 2px 5px;text-align:center;">';
		$html[] = '<strong style="color:#000000;">';
		$html[] = $text;
		$html[] = '</strong>';
		$html[] = ': <span style="white-space:nowrap;"><a href="http://www.nonumber.nl/go-pro?ext='.$alias.'" target="_blank"><em>';
		$html[] = html_entity_decode(JText::_('NN_GO_PRO'), ENT_COMPAT, 'UTF-8');
		$html[] = '</em></a></span>';
		$html[] = '</div>';

		return implode('', $html);
	}

	/**
	 * Return an empty extension item
	 */
	function getVersionType($element)
	{
		jimport('joomla.filesystem.file');

		switch ($element) {
			case 'advancedmodulemanager':
				$element = 'advancedmodules';
				break;
			case 'nonumberextensionmanager':
				$element = 'nonumbermanager';
				break;
			case 'whatnothing':
				$element = 'what-nothing';
				break;
		}
		$file = '';
		$xml = '';

		// Components
		if (!$file) {
			if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_'.$element.'/'.$element.'.xml')) {
				$xml = JPATH_ADMINISTRATOR.'/components/com_'.$element.'/'.$element.'.xml';
			} else if (JFile::exists(JPATH_SITE.'/components/com_'.$element.'/'.$element.'.xml')) {
				$xml = JPATH_SITE.'/components/com_'.$element.'/'.$element.'.xml';
			} else if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_'.$element.'/com_'.$element.'.xml')) {
				$xml = JPATH_ADMINISTRATOR.'/components/com_'.$element.'/com_'.$element.'.xml';
			} else if (JFile::exists(JPATH_SITE.'/components/com_'.$element.'/com_'.$element.'.xml')) {
				$xml = JPATH_SITE.'/components/com_'.$element.'/com_'.$element.'.xml';
			}
			if ($xml) {
				$file = $xml;
			}
		}

		// System Plugins
		if (!$file) {
			if (JFile::exists(JPATH_PLUGINS.'/system/'.$element.'/'.$element.'.xml')) {
				$xml = JPATH_PLUGINS.'/system/'.$element.'/'.$element.'.xml';
			} else if (JFile::exists(JPATH_PLUGINS.'/system/'.$element.'.xml')) {
				$xml = JPATH_PLUGINS.'/system/'.$element.'.xml';
			}
			if ($xml) {
				$file = $xml;
			}
		}

		// Editor Button Plugins
		if (!$file) {
			if (JFile::exists(JPATH_PLUGINS.'/editors-xtd/'.$element.'/'.$element.'.xml')) {
				$xml = JPATH_PLUGINS.'/editors-xtd/'.$element.'/'.$element.'.xml';
			} else if (JFile::exists(JPATH_PLUGINS.'/editors-xtd/'.$element.'.xml')) {
				$xml = JPATH_PLUGINS.'/editors-xtd/'.$element.'.xml';
			}
			if ($xml) {
				$file = $xml;
			}
		}
		// Modules
		if (!$file) {
			if (JFile::exists(JPATH_ADMINISTRATOR.'/modules/mod_'.$element.'/'.$element.'.xml')) {
				$xml = JPATH_ADMINISTRATOR.'/modules/mod_'.$element.'/'.$element.'.xml';
			} else if (JFile::exists(JPATH_SITE.'/modules/mod_'.$element.'/'.$element.'.xml')) {
				$xml = JPATH_SITE.'/modules/mod_'.$element.'/'.$element.'.xml';
			} else if (JFile::exists(JPATH_ADMINISTRATOR.'/modules/mod_'.$element.'/mod_'.$element.'.xml')) {
				$xml = JPATH_ADMINISTRATOR.'/modules/mod_'.$element.'/mod_'.$element.'.xml';
			} else if (JFile::exists(JPATH_SITE.'/modules/mod_'.$element.'/mod_'.$element.'.xml')) {
				$xml = JPATH_SITE.'/modules/mod_'.$element.'/mod_'.$element.'.xml';
			}
			if ($xml) {
				$file = $xml;
			}
		}

		$type = 'old';
		if ($file) {
			$xml = JApplicationHelper::parseXMLInstallFile($file);
			if ($xml && isset($xml['version'])) {
				if (!(stripos($xml['version'], 'FREE') === false)) {
					$type = 'free';
				} else if (!(stripos($xml['version'], 'PRO') === false)) {
					$type = 'pro';
				}
			}
		}

		return $type;
	}
}
