<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Client Language List field.
 *
 * @since  3.9.0
 */
class JFormFieldLanguageclient extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.9.0
	 */
	protected $type = 'Languageclient';

	/**
	 * Cached form field options.
	 *
	 * @var		array
	 * @since   3.9.0
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.9.0
	 */
	protected function getOptions()
	{
		// Try to load the data from our mini-cache.
		if (!empty($this->cache))
		{
			return $this->cache;
		}

		// Get all languages of frontend and backend.
		$languages       = array();
		$site_languages  = JLanguageHelper::getKnownLanguages(JPATH_SITE);
		$admin_languages = JLanguageHelper::getKnownLanguages(JPATH_ADMINISTRATOR);

		// Create a single array of them.
		foreach ($site_languages as $tag => $language)
		{
			$languages[$tag . '0'] = JText::sprintf('COM_LANGUAGES_VIEW_OVERRIDES_LANGUAGES_BOX_ITEM', $language['name'], JText::_('JSITE'));
		}

		foreach ($admin_languages as $tag => $language)
		{
			$languages[$tag . '1'] = JText::sprintf('COM_LANGUAGES_VIEW_OVERRIDES_LANGUAGES_BOX_ITEM', $language['name'], JText::_('JADMINISTRATOR'));
		}

		// Sort it by language tag and by client after that.
		ksort($languages);

		// Add the languages to the internal cache.
		$this->cache = array_merge(parent::getOptions(), $languages);

		return $this->cache;
	}
}
