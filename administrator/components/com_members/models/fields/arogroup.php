<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.html.html');
jimport('joomla.form.fields.list');

/**
 * Form Field Type Class for a legacy User Group.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @version		1.6
 */
class JFormFieldUserGroup extends JFormFieldList
{
	function _getOptions()
	{
		// Get a database object.
		$db = &JFactory::getDBO();

		// Get the user groups from the database.
		$db->setQuery(
			'SELECT a.id AS value, a.name AS text, COUNT(DISTINCT b.id) AS level' .
			' FROM #__core_acl_aro_groups AS a' .
			' LEFT JOIN `#__core_acl_aro_groups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' LEFT JOIN `#__core_acl_aro_groups` AS p ON p.id = 28' .
			' WHERE a.lft > p.lft AND a.rgt < p.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i=0,$n=count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('&nbsp;&nbsp;',($options[$i]->level - 2)).$options[$i]->text;
		}

		// If all usergroups is allowed, push it into the array.
		if ($this->_element->attributes('allow_all') == 'true') {
			array_unshift($options, JHtml::_('select.option', '', JText::_('JX Show All Groups')));
		}

		return $options;
	}
}