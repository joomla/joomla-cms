<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of published content languages with home pages
 *
 * @see    JFormFieldLanguage for a select list of application languages.
 * @since  3.5
 */
class JFormFieldFrontend_Language extends JFormFieldList
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
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.lang_code AS value, a.title AS text')
				->from($db->quoteName('#__languages') . ' AS a')
				->where('a.published = 1')
				->order('a.title');

		// Select the language home pages.
		$query->select('l.home, l.language')
			->innerJoin($db->quoteName('#__menu') . ' AS l ON l.language=a.lang_code AND l.home=1 AND l.published=1 AND l.language <> ' . $db->quote('*'))
			->innerJoin($db->quoteName('#__extensions') . ' AS e ON e.element = a.lang_code')
			->where('e.client_id = 0')
			->where('e.enabled = 1')
			->where('e.state = 0');

		$db->setQuery($query);

		try
		{
			$languages = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$languages = array();

			if (JFactory::getUser()->authorise('core.admin'))
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(
			parent::getOptions(),
			$languages
		);

		return $options;
	}
}
