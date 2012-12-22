<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 * @since       3.1
 */
class JFormFieldTag extends JFormFieldList
{
	/**
	 * A flexible category list that respects access controls
	 *
	 * @var		string
	 * @since	3.1
	 */
	public $type = 'Tag';

	/**
	 * Method to get a list of categories that respects access controls and can be used for
	 * either category assignment or parent category assignment in edit screens.
	 * Use the parent element to indicate that the field will be used for assigning parent categories.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.1
	 */
	protected function getOptions()
	{
		$options = array();
		$published = $this->element['published']? $this->element['published'] : array(0,1);
		$name = (string) $this->element['name'];


		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text, a.level, a.published');
		$query->from('#__tags AS a');
		$query->join('LEFT', $db->quoteName('#__tags').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter language
		if (!empty($this->element['language']))
		{
			$query->where('a.language = ' . $db->q($this->element['language']));

		}

		// Filter on the published state

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$query->where('a.published IN (' . implode(',', $published) . ')');
		}

		$query->group('a.id, a.title, a.level, a.lft, a.rgt, a.parent_id, a.published');
		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();

		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
