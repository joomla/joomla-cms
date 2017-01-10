<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die();

use Joomla\Utilities\ArrayHelper;

JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

JFormHelper::loadFieldClass('list');

/**
 * Content Types Filter field for the Finder package.
 *
 * @since  3.6.0
 */
class JFormFieldContentTypes extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.6.0
	 */
	protected $type = 'ContentTypes';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.6.0
	 */
	public function getOptions()
	{
		$lang    = JFactory::getLanguage();
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id', 'value'))
			->select($db->quoteName('title', 'text'))
			->from($db->quoteName('#__finder_types'));

		// Get the options.
		$db->setQuery($query);

		try
		{
			$contentTypes = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $db->getMessage());
		}

		// Translate.
		foreach ($contentTypes as $contentType)
		{
			$key = FinderHelperLanguage::branchSingular($contentType->text);
			$contentType->translatedText = $lang->hasKey($key) ? JText::_($key) : $contentType->text;
		}

		// Order by title.
		$contentTypes = ArrayHelper::sortObjects($contentTypes, 'translatedText', 1, true, true);

		// Convert the values to options.
		foreach ($contentTypes as $contentType)
		{
			$options[] = JHtml::_('select.option', $contentType->value, $contentType->translatedText);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
