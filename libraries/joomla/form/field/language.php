<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports a list of installed application languages
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @see         JFormFieldContentLanguage for a select list of content languages.
 * @since       11.1
 */
class JFormFieldLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Language';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize some field attributes.
		$client = (string) $this->element['client'];

		if ($client != 'site' && $client != 'administrator')
		{
			$client = 'site';
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(
			parent::getOptions(),
			JLanguageHelper::createLanguageList($this->value, constant('JPATH_' . strtoupper($client)), true, true)
		);

		// Set the default value active language
		if ($langParams = JComponentHelper::getParams('com_languages'))
		{
			switch ((string) $this->value)
			{
				case 'site':
				case 'frontend':
				case '0':
					$this->value = $langParams->get('site', 'en-GB');
					break;
				case 'admin':
				case 'administrator':
				case 'backend':
				case '1':
					$this->value = $langParams->get('administrator', 'en-GB');
					break;
				case 'active':
				case 'auto':
					$lang = JFactory::getLanguage();
					$this->value = $lang->getTag();
					break;
				default:
				break;
			}
		}

		return $options;
	}
}
