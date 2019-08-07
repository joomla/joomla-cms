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
 * Content Association Languages Field class.
 *
 * @since  4.0
 */
class ContentdefaultassoclangField extends ContentlanguageField
{
	/**
	 * The list field type.
	 *
	 * @var    string
	 *
	 * @since  4.0
	 */
	public $type = 'contentdefaultassoclang';

	/**
	 * Method to get the field options.
	 *
	 * @return  array The field option objects.
	 *
	 * @since   4.0
	 */
	public function getOptions()
	{
		$defaultAssocLang = Associations::getDefaultAssocLang();
		$contentLanguages = LanguageHelper::getContentLanguages(array(0, 1));

		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_LANGUAGE'));

		foreach ($contentLanguages as $langCode)
		{
			// Add information to the language if it is the default association language.
			if ($langCode->lang_code == $defaultAssocLang)
			{
				$options[] = HTMLHelper::_('select.option', $langCode->lang_code, $langCode->title . ' - ' . Text::_('JGLOBAL_ASSOCIATIONS_DEFAULT_ASSOC_LANG'));
			}
			else
			{
				$options[] = HTMLHelper::_('select.option', $langCode->lang_code, $langCode->title);
			}
		}

		return $options;
	}
}
