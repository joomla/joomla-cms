<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ContentlanguageField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;


/**
 * Content Master Languages Field class.
 *
 * @since  4.0
 */
class ContentmasterlanguageField extends ContentlanguageField
{
	/**
	 * The list field type.
	 *
	 * @var    string
	 *
	 * @since  4.0
	 */
	public $type = 'contentmasterlanguage';

	/**
	 * Method to get the field options.
	 *
	 * @return  array The field option objects.
	 *
	 * @since   4.0
	 */
	public function getOptions()
	{
		$globalMasterLang = Associations::getGlobalMasterLanguage();

		$contentLanguages = LanguageHelper::getContentLanguages(array(0, 1));
		$options = array();

		$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_LANGUAGE'));

		foreach ($contentLanguages as $langCode)
		{
			// Add information to the language if it is the global master language
			if ($langCode->lang_code == $globalMasterLang)
			{
				$options[] = HTMLHelper::_('select.option', $langCode->lang_code, $langCode->title . ' - ' . Text::_('JGLOBAL_ASSOCIATIONS_MASTER_LANGUAGE'));
			}
			else
			{
				$options[] = HTMLHelper::_('select.option', $langCode->lang_code, $langCode->title);
			}
		}

		return $options;
	}
}
