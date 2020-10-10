<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Provides a list of content languages
 *
 * @see    \Joomla\CMS\Form\Field\LanguageField for a select list of application languages.
 * @since  1.6
 */
class ContentlanguageField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'ContentLanguage';

	/**
	 * Method to get the field options for content languages.
	 *
	 * @return  array  The options the field is going to show.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), HTMLHelper::_('contentlanguage.existing'));
	}
}
