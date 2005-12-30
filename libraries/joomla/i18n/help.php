<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/


/**
* Help system class
* @package Joomla
* @subpackage Help
* @since 1.1
*/
class JHelp {

	/**
	* Create an URL for a giving help file reference
	* @param string The name of the popup file (excluding the file extension for an xml file)
	* @param boolean Use the help file in the component directory
	*/
	function createURL($ref, $com=false)
	{
		global $mainframe, $_VERSION, $option;

		$helpUrl 	= $mainframe->getCfg('helpurl');

		if ($com) {
	   		// help file for 3PD Components
			$url = JURL_SITE . '/administrator/components/' . $option. '/help/';
			if (!eregi( '\.html$', $ref )) {
				$ref = $ref . '.html';
			}
			$url .= $ref;
		} else if ( $helpUrl ) {
	   		// Online help site as defined in GC
			$ref .= $_VERSION->getHelpVersion();
			$url = $helpUrl . '/index2.php?option=com_content&amp;task=findkey&amp;pop=1&amp;keyref=' . urlencode( $ref );
		} else {
	   		// Included html help files
			$url = JURL_SITE . '/administrator/help/eng_GB/';
			$ref = $ref . '.html';
			$url .= $ref;
		}

		return $url;
	}

	/**
	 * Builds a list of the help sites which can be used in a select option
	 * @param string	Path to an xml file
	 * @param string	Language tag to select (if exists)
	 * @param array	An array of arrays ( text, value, selected )
	 */
	function createSiteList($pathToXml, $selected = null)
	{
		$list = array ();

		$xmlDoc = JFactory::getXMLParser();
		$xmlDoc->resolveErrors(true);
		$xml = JFile::read($pathToXml);

		if(!$xml) {
			$option['text'] = 'English (GB) help.joomla.org';
			$option['value'] = 'http://help.joomla.org';
			$list[] = $option;
		} else {

			if($xmlDoc->parseXML($xml, false, true)) {
				$root = & $xmlDoc->documentElement;

				// Are there any languages??
				$elmSites = & $root->getElementsByPath('sites', 1);

				if (is_object($elmSites)) {

					$option = array ();
					$sites = $elmSites->childNodes;
					foreach ($sites as $site) {

						$option['text'] = $site->getText();
						$option['value'] = $site->getAttribute('url');
						$list[] = $option;
					}
				}
			}
		}

		return $list;
	}
}
?>