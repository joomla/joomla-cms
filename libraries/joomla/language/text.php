<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Language
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Text handling class.
 *
 * @since       11.1
 * @deprecated  use the translator object of the application
 */
class JText
{
	/**
	 * Translates a string into the current language.
	 *
	 * Examples:
	 * `<script>alert(Joomla.JText._('<?php echo JText::_("JDEFAULT", array("script"=>true)); ?>'));</script>`
	 * will generate an alert message containing 'Default'
	 * `<?php echo JText::_("JDEFAULT"); ?>` will generate a 'Default' string
	 *
	 * @param   string   $string                The string to translate.
	 * @param   mixed    $jsSafe                Boolean: Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation)
	 * @param   boolean  $script                To indicate that the string will be push in the javascript language store
	 *
	 * @return  string  The translated string or the key if $script is true
	 *
	 * @since       11.1
	 * @deprecated  use the translator object of the application
	 */
	public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		return JFactory::getApplication()->getTranslator()->translate($string, $jsSafe, $interpretBackSlashes, $script);
	}

	/**
	 * Translates a string into the current language.
	 *
	 * Examples:
	 * `<?php echo JText::alt('JALL', 'language'); ?>` will generate a 'All' string in English but a "Toutes" string in French
	 * `<?php echo JText::alt('JALL', 'module'); ?>` will generate a 'All' string in English but a "Tous" string in French
	 *
	 * @param   string   $string                The string to translate.
	 * @param   string   $alt                   The alternate option for global string
	 * @param   mixed    $jsSafe                Boolean: Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation)
	 * @param   boolean  $script                To indicate that the string will be pushed in the javascript language store
	 *
	 * @return  string  The translated string or the key if $script is true
	 *
	 * @since       11.1
	 * @deprecated  use the translator object of the application
	 */
	public static function alt($string, $alt, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		return JFactory::getApplication()->getTranslator()->alt($string, $alt, $jsSafe, $interpretBackSlashes, $script);
	}

	/**
	 * Like JText::sprintf but tries to pluralise the string.
	 *
	 * Note that this method can take a mixed number of arguments as for the sprintf function.
	 *
	 * The last argument can take an array of options:
	 *
	 * array('jsSafe'=>boolean, 'interpretBackSlashes'=>boolean, 'script'=>boolean)
	 *
	 * where:
	 *
	 * jsSafe is a boolean to generate a javascript safe strings.
	 * interpretBackSlashes is a boolean to interpret backslashes \\->\, \n->new line, \t->tabulation.
	 * script is a boolean to indicate that the string will be push in the javascript language store.
	 *
	 * Examples:
	 * `<script>alert(Joomla.JText._('<?php echo JText::plural("COM_PLUGINS_N_ITEMS_UNPUBLISHED", 1, array("script"=>true)); ?>'));</script>`
	 * will generate an alert message containing '1 plugin successfully disabled'
	 * `<?php echo JText::plural('COM_PLUGINS_N_ITEMS_UNPUBLISHED', 1); ?>` will generate a '1 plugin successfully disabled' string
	 *
	 * @param   string   $string  The format string.
	 * @param   integer  $n       The number of items
	 *
	 * @return  string  The translated strings or the key if 'script' is true in the array of options
	 *
	 * @since       11.1
	 * @deprecated  use the translator object of the application
	 */
	public static function plural($string, $n)
	{
		return JFactory::getApplication()->getTranslator()->plural($string, $n);
	}

	/**
	 * Passes a string thru a sprintf.
	 *
	 * Note that this method can take a mixed number of arguments as for the sprintf function.
	 *
	 * The last argument can take an array of options:
	 *
	 * array('jsSafe'=>boolean, 'interpretBackSlashes'=>boolean, 'script'=>boolean)
	 *
	 * where:
	 *
	 * jsSafe is a boolean to generate a javascript safe strings.
	 * interpretBackSlashes is a boolean to interpret backslashes \\->\, \n->new line, \t->tabulation.
	 * script is a boolean to indicate that the string will be push in the javascript language store.
	 *
	 * @param   string  $string  The format string.
	 *
	 * @return  string  The translated strings or the key if 'script' is true in the array of options.
	 *
	 * @since       11.1
	 * @deprecated  use the translator object of the application
	 */
	public static function sprintf($string)
	{
		return call_user_func_array(array(JFactory::getApplication()->getTranslator(), 'sprintf'), func_get_args());
	}

	/**
	 * Passes a string thru an printf.
	 *
	 * Note that this method can take a mixed number of arguments as for the sprintf function.
	 *
	 * @param   format  $string  The format string.
	 *
	 * @return  mixed
	 *
	 * @since       11.1
	 * @deprecated  use the translator object of the application
	 */
	public static function printf($string)
	{
		return call_user_func_array(array(JFactory::getApplication()->getTranslator(), 'printf'), func_get_args());
	}

	/**
	 * Translate a string into the current language and stores it in the JavaScript language store.
	 *
	 * @param   string   $string                The JText key.
	 * @param   boolean  $jsSafe                Ensure the output is JavaScript safe.
	 * @param   boolean  $interpretBackSlashes  Interpret \t and \n.
	 *
	 * @return  string
	 *
	 * @since       11.1
	 * @deprecated  use the translator object of the application
	 */
	public static function script($string = null, $jsSafe = false, $interpretBackSlashes = true)
	{
		return JFactory::getApplication()->getTranslator()->script($string, $jsSafe, $interpretBackSlashes);
	}

	/**
	 * Get the strings that have been loaded to the JavaScript language store.
	 *
	 * @return  array
	 *
	 * @since       3.7.0
	 * @deprecated  use the translator object of the application
	 */
	public static function getScriptStrings()
	{
		return JFactory::getApplication()->getTranslator()->getScriptStrings();
	}
}
