<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once JPATH_LIBRARIES.DS.'joomla'.DS.'form'.DS.'fields'.DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class JFormFieldCategoryParent extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'CategoryParent';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$db		= &JFactory::getDbo();
		$query	= new JQuery;

		$query->select('a.id AS value, a.title AS text, a.level');
		$query->from('#__categories AS a');
		$query->join('LEFT', '`#__categories` AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter by the type
		if ($extension = $this->_form->getValue('extension')) {
			$query->where('(a.extension = '.$db->quote($extension).' OR a.parent_id = 0)');
		}

		// Prevent parenting to children of this item.
		if ($id = $this->_form->getValue('id')) {
			$query->join('LEFT', '`#__categories` AS p ON p.id = '.(int) $id);
			$query->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');
		}

		$query->group('a.id');
		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++) {
			$options[$i]->text = str_repeat('- ',$options[$i]->level).$options[$i]->text;
		}

		$options	= array_merge(
						parent::_getOptions(),
						$options
					);

		return $options;
	}
}