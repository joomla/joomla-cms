<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;

/**
 * Provides a list of published content languages with home pages
 *
 * @see    \Joomla\CMS\Form\Field\LanguageField for a select list of application languages.
 * @since  3.5
 */
class FrontendlanguageField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $type = 'Frontend_Language';

	/**
	 * Method to get the field options for frontend published content languages with homes.
	 *
	 * @return  array  The options the field is going to show.
	 *
	 * @since   3.5
	 */
	protected function getOptions()
	{
		// Get the database object and a new query object.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('a.lang_code', 'value'),
				$db->quoteName('a.title', 'text'),
			]
		)
			->from($db->quoteName('#__languages', 'a'))
			->where(
				[
					$db->quoteName('a.published') . ' = 1',
					$db->quoteName('e.client_id') . ' = 0',
					$db->quoteName('e.enabled') . ' = 1',
					$db->quoteName('e.state') . ' = 0',
				]
			)
			->order($db->quoteName('a.title'));

		// Select the language home pages.
		$query->join(
				'INNER',
				$db->quoteName('#__menu', 'l'),
				$db->quoteName('l.language') . ' = ' . $db->quoteName('a.lang_code'). ' AND ' . $db->quoteName('l.home') . ' = ' . $db->quote('1')
				. ' AND ' . $db->quoteName('l.published') . ' = 1 AND ' . $db->quotename('l.language') . ' <> ' . $db->quote('*')
			)
			->join('INNER', $db->quoteName('#__extensions', 'e'), $db->quoteName('e.element') . ' = ' . $db->quoteName('a.lang_code'));

		$db->setQuery($query);

		try
		{
			$languages = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			$languages = array();

			if (Factory::getUser()->authorise('core.admin'))
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $languages);
	}
}
