<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Language
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Help system class
 *
 * @package 		Joomla.Framework
 * @subpackage	Language
 * @since		1.5
 */
class JHelp
{

	/**
	* Create an URL for a giving help file reference
	*
	* @param string The name of the popup file (excluding the file extension for an xml file)
	* @param boolean Use the help file in the component directory
	*/
	static function createURL($ref, $useComponent = false)
	{
		$component		= JApplicationHelper::getComponentName();
		$app			= &JFactory::getApplication();
		$user			= &JFactory::getUser();
		$userHelpUrl	= $user->getParam('helpsite');
		$globalHelpUrl 	= $app->getCfg('helpurl');
		$lang			= &JFactory::getLanguage();

		if ($useComponent) {
			if (!preg_match('#\.html$#i', $ref)) {
				$ref = $ref . '.html';
			}

			$url = 'components/' . $component. '/help';
			$tag =  $lang->getTag();

			// Check if the file exists within a different language!
			if ($lang->getTag() != 'en-GB') {
				$localeURL = JPATH_BASE.DS.$url.DS.$tag.DS.$ref;
				jimport('joomla.filesystem.file');
				if (!JFile::exists($localeURL)) {
					$tag = 'en-GB';
				}
			}
			return $url.'/'.$tag.'/'.$ref;
		}


		if ($userHelpUrl)
		{
			// Online help site as defined in GC
			$version = new JVersion();
			$ref .= $version->getHelpVersion();
			$url = $userHelpUrl . '/index2.php?option=com_content&amp;task=findkey&amp;tmpl=component&amp;keyref=' . urlencode($ref);
		}
		else if ($globalHelpUrl)
		{
			// Online help site as defined in GC
			$version = new JVersion();
			$ref .= $version->getHelpVersion();
			$url = $globalHelpUrl . '/index2.php?option=com_content&amp;task=findkey&amp;tmpl=component;1&amp;keyref=' . urlencode($ref);
		}
		else
		{
			// Included html help files
			$helpURL = 'help/' .$lang->getTag() .'/';

			if (!eregi('\.html$', $ref)) {
				$ref = $ref . '.html';
			}

			// Check if the file exists within a different language!
			if ($lang->getTag() != 'en-GB') {
				$localeURL = JPATH_BASE . $helpURL .$ref;
				jimport('joomla.filesystem.file');
				if (!JFile::exists($localeURL)) {
					$helpURL = 'help/en-GB/';
				}
			}
			$url = $helpURL . $ref;
		}

		return $url;
	}

	/**
	 * Builds a list of the help sites which can be used in a select option
	 *
	 * @param string	Path to an xml file
	 * @param string	Language tag to select (if exists)
	 * @param array	An array of arrays (text, value, selected)
	 */
	static function createSiteList($pathToXml, $selected = null)
	{
		$list	= array ();
		$xml	= &JFactory::getXMLParser('Simple');
		$data	= null;
		if (!empty($pathToXml)) {
			$data = file_get_contents($pathToXml);
		}

		if (empty($data))
		{
			$option['text'] = 'English (GB) help.joomla.org';
			$option['value'] = 'http://help.joomla.org';
			$list[] = $option;
		}
		else
		{
			if ($xml->loadString($data))
			{
				// Are there any languages??
				$elmSites = & $xml->document->sites[0];

				if (is_object($elmSites)) {

					$option = array ();
					$sites = $elmSites->children();
					foreach ($sites as $site)
					{
						$text				= $site->data();
						$url				= $site->attributes('url');
						$option['text']		= $text;
						$option['value']	= $url;
						$list[]				= $option;
					}
				}
			}
		}

		return $list;
	}
}
