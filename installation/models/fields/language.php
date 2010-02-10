<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');

/**
 * Language Form Field class.
 *
 * @package		Joomla.Installation
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldLanguage extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Language';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		// Initialise variables.
		$app = & JFactory::getApplication();

		// Detect the native language.
		jimport('joomla.language.helper');
		$native = JLanguageHelper::detectLanguage();
		if(empty($native)) {
			$native = 'en-GB';
		}

		// Get a forced language if it exists.
		$forced = $app->getLocalise();
		if (!empty($forced['lang']))
		{
			$native = $forced['lang'];
		}

		// Get the list of available languages.
		$options = JLanguageHelper::createLanguageList($native);
		if (!$options || JError::isError($options)) {
			$options = array();
		}

		// Set the default value from the native language.
		$this->value = $native;

		// Merge in any explicitly listed options from the XML definition.
		$options = array_merge(parent::_getOptions(), $options);

		return $options;
	}
}
