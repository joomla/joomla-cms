<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Languages Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionLanguages
{
	protected $type = 'Languages';

	/**
	 * Method to get a list of options.
	 *
	 * @param   SimpleXMLElement  $option     <option/> element
	 * @param   string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		// Initialize some field attributes.
		$client = (string) $option['client'];

		if ($client != 'site' && $client != 'administrator')
		{
			$client = 'site';
		}

		// Get the current language and client path.
		$language = JLanguageHelper::detectLanguage();
		$path = constant('JPATH_' . strtoupper($client));

		// A list of language options.
		$options = array();

		// For some reason we get a list of associative arrays, we need objects.
		foreach (JLanguageHelper::createLanguageList($language, $path, true, true) as $lang)
		{
			$options[] = (object) $lang;
		}

		return $options;
	}
}
