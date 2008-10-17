<?php
/**
* @version 1.5
* @package com_fftranslation
* @author Ifan Evans
* @copyright Copyright (C) 2007 Ifan Evans. All rights reserved.
* @license GNU/GPL
* @bugs - please report to post@ffenest.co.uk
*/

/**
 * Renders a reference language element
 * Use instead of the joomla library languages element, which only lists languages for one client
 */
class JElementffLanguages extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'ffLanguages';

	function fetchElement($name, $value, &$node, $control_name)
	{
		# cache activation
		$cache = & JFactory::getCache('JLanguage');
		$admin = $cache->call('JLanguage::getKnownLanguages', JPATH_ADMINISTRATOR);
		$site = $cache->call('JLanguage::getKnownLanguages', JPATH_SITE);

		# only languages in both site and admin can be reference languages
		$languages = array();
		foreach($admin as $k=>$v) if (isset($site[$k])) $languages[] = array('value'=>$k,'text'=>$v['name']);

		# return the select box
		return JHTMLSelect::genericList($languages, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, $control_name.$name );
	}
}
?>