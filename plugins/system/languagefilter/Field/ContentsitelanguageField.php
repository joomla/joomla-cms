<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagefilter
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Languagefilter\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\ContentlanguageField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;


/**
 * Content Site Languages Field class.
 *
 * @since  4.0
 */
class ContentsitelanguageField extends ContentlanguageField
{
	/**
	 * The list field type.
	 *
	 * @var    string
	 *
	 * @since  4.0
	 */
	public $type = 'contentsitelanguage';

	/**
	 * Method to get the field options.
	 *
	 * @return  array The field option objects.
	 *
	 * @since   4.0
	 */
	public function getOptions()
	{
		$defaultSiteLanguage = ComponentHelper::getParams('com_languages')->get('site');
		$contentLanguages = LanguageHelper::getContentLanguages(array(0, 1));
		$options = array();

		foreach ($contentLanguages as $langCode)
		{
			// Add the information to the language if it is the default site language
			if ($langCode->lang_code == $defaultSiteLanguage)
			{
				$options[] = HTMLHelper::_('select.option', $langCode->lang_code, $langCode->title
					. ' - ' . Text::_('PLG_SYSTEM_LANGUAGEFILTER_OPTION_DEFAULT_LANGUAGE')
				);
			}
			else
			{
				$options[] = HTMLHelper::_('select.option', $langCode->lang_code, $langCode->title);
			}
		}

		return $options;
	}
}
