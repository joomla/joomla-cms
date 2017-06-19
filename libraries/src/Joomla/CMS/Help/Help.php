<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Help;

defined('JPATH_PLATFORM') or die;

/**
 * Help system class
 *
 * @since  1.5
 */
class Help
{
	/**
	 * Create a URL for a given help key reference
	 *
	 * @param   string   $ref           The name of the help screen (its key reference)
	 * @param   boolean  $useComponent  Use the help file in the component directory
	 * @param   string   $override      Use this URL instead of any other
	 * @param   string   $component     Name of component (or null for current component)
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function createUrl($ref, $useComponent = false, $override = null, $component = null)
	{
		$local = false;
		$app   = \JFactory::getApplication();

		if ($component === null)
		{
			$component = \JApplicationHelper::getComponentName();
		}

		//  Determine the location of the help file.  At this stage the URL
		//  can contain substitution codes that will be replaced later.

		if ($override)
		{
			$url = $override;
		}
		else
		{
			// Get the user help URL.
			$user = \JFactory::getUser();
			$url  = $user->getParam('helpsite');

			// If user hasn't specified a help URL, then get the global one.
			if ($url == '')
			{
				$url = $app->get('helpurl');
			}

			// Component help URL overrides user and global.
			if ($useComponent)
			{
				// Look for help URL in component parameters.
				$params = \JComponentHelper::getParams($component);
				$url    = $params->get('helpURL');

				if ($url == '')
				{
					$local = true;
					$url   = 'components/{component}/help/{language}/{keyref}';
				}
			}

			// Set up a local help URL.
			if (!$url)
			{
				$local = true;
				$url   = 'help/{language}/{keyref}';
			}
		}

		// If the URL is local then make sure we have a valid file extension on the URL.
		if ($local)
		{
			if (!preg_match('#\.html$|\.xml$#i', $ref))
			{
				$url .= '.html';
			}
		}

		/*
		 *  Replace substitution codes in the URL.
		 */
		$lang    = \JFactory::getLanguage();
		$version = new \JVersion;
		$jver    = explode('.', $version->getShortVersion());
		$jlang   = explode('-', $lang->getTag());

		$debug  = $lang->setDebug(false);
		$keyref = \JText::_($ref);
		$lang->setDebug($debug);

		// Replace substitution codes in help URL.
		$search = array(
			// Application name (eg. 'Administrator')
			'{app}',
			// Component name (eg. 'com_content')
			'{component}',
			// Help screen key reference
			'{keyref}',
			// Full language code (eg. 'en-GB')
			'{language}',
			// Short language code (eg. 'en')
			'{langcode}',
			// Region code (eg. 'GB')
			'{langregion}',
			// Joomla major version number
			'{major}',
			// Joomla minor version number
			'{minor}',
			// Joomla maintenance version number
			'{maintenance}',
		);

		$replace = array(
			// {app}
			$app->getName(),
			// {component}
			$component,
			// {keyref}
			$keyref,
			// {language}
			$lang->getTag(),
			// {langcode}
			$jlang[0],
			// {langregion}
			$jlang[1],
			// {major}
			$jver[0],
			// {minor}
			$jver[1],
			// {maintenance}
			$jver[2],
		);

		// If the help file is local then check it exists.
		// If it doesn't then fallback to English.
		if ($local)
		{
			$try = str_replace($search, $replace, $url);

			if (!is_file(JPATH_BASE . '/' . $try))
			{
				$replace[3] = 'en-GB';
				$replace[4] = 'en';
				$replace[5] = 'GB';
			}
		}

		$url = str_replace($search, $replace, $url);

		return $url;
	}

	/**
	 * Builds a list of the help sites which can be used in a select option.
	 *
	 * @param   string  $pathToXml  Path to an XML file.
	 *
	 * @return  array  An array of arrays (text, value, selected).
	 *
	 * @since   1.5
	 */
	public static function createSiteList($pathToXml)
	{
		$list = array();
		$xml  = false;

		if (!empty($pathToXml))
		{
			$xml = simplexml_load_file($pathToXml);
		}

		if (!$xml)
		{
			$option['text']  = 'English (GB) help.joomla.org';
			$option['value'] = 'http://help.joomla.org';

			$list[] = $option;
		}
		else
		{
			$option = array();

			foreach ($xml->sites->site as $site)
			{
				$option['text']  = (string) $site;
				$option['value'] = (string) $site->attributes()->url;

				$list[] = $option;
			}
		}

		return $list;
	}
}
