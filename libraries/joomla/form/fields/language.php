<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports a list of installed application languages
 *
 * @see    JFormFieldContentLanguage for a select list of content languages.
 * @since  1.7.0
 */
class JFormFieldLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Language';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.7.0
	 */
	protected function getOptions()
	{
		// Initialize some field attributes.
		$client = (string) $this->element['client'];

		if ($client != 'site' && $client != 'administrator')
		{
			$client = 'site';
		}

		// Make sure the languages are sorted base on locale instead of random sorting
		$languages = JLanguageHelper::createLanguageList($this->value, constant('JPATH_' . strtoupper($client)), true, true);
		if (count($languages) > 1)
		{
			usort(
				$languages,
				function ($a, $b)
				{
					return strcmp($a['value'], $b['value']);
				}
			);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(
			parent::getOptions(),
			$languages
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
