<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;

/**
 * Form Field class for the Joomla Platform.
 * Supports a list of installed application languages
 *
 * @see    \Joomla\CMS\Form\Field\ContentlanguageField for a select list of content languages.
 * @since  1.7.0
 */
class LanguageField extends ListField
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

		if ($client !== 'site' && $client !== 'administrator')
		{
			$client = 'site';
		}

		// Make sure the languages are sorted base on locale instead of random sorting
		$languages = LanguageHelper::createLanguageList($this->value, \constant('JPATH_' . strtoupper($client)), true, true);

		if (\count($languages) > 1)
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
		if ($langParams = ComponentHelper::getParams('com_languages'))
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
					$lang = Factory::getLanguage();
					$this->value = $lang->getTag();
					break;
				default:
				break;
			}
		}

		return $options;
	}
}
