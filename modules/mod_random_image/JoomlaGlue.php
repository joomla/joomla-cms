<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_random_image
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 *	ModRandomImageGlue
 *
 *	This class abstracts all the calls into the Joomla API from the actual
 *	module code. This means the module can be run completely isolated from
 *	Joomla itself.
 */
class ModRandomImageGlue
{
	/**
	 *	getBaseURL
	 *
	 *	Gets the domain (plus '/administrator' if on the admin site) of the site
	 *	URL
	 *
	 *	return	string
	 */
	public function getBaseURL()
	{
		return JURI::base();
	}
	/**
	 *	strpos
	 *
	 *	Calls a UTF-8 aware string search function
	 *
	 *	@param	needle		string to be searched for
	 *	@param	haystack	string to search within
	 *	@param	offset		offset into haystack to start searching
	 *
	 *	@return	mixed	NUmber of chars before first match, or false if not found
	 */
	public function strpos($needle, $haystack, $offset=false)
	{
		return JString::strpos($needle, $haystack, $offset);
	}
	/**
	 *	getTranslatedText
	 *
	 *	Gets the text string in the current language
	 *
	 * @param   string   $string                The string to translate.
	 * @param   mixed    $jsSafe                Boolean: Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation)
	 * @param   boolean  $script                To indicate that the string will be push in the javascript language store
	 *
	 * @return  string  The translated string or the key is $script is true
	 */
	public function getTranslatedText($string, $jsSafe = false, 
		$interpretBackSlashes = true, $script = false)
	{
		return JText::_($string, $jsSafe, $interpretBackSlashes, $script);
	}
	/**
	 *	getLayoutPath
	 *
	 *	Gets the text string in the current language
	 *
	 * @param   string   $layout	The layout name to find.
	 *
	 * @return  string  The path to the layout file.
	 */
	public function getLayoutPath($layout)
	{
		return JModuleHelper::getLayoutPath('mod_random_image', $layout);
	}
}