<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

/**
 * HTML behavior class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class JHTMLFilter
{
	function state($active = null)
	{
		// Build the filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '', JText::_('Users_Filter_State'));
		$options[]	= JHtml::_('select.option', '0', JText::_('Users_Enabled'));
		$options[]	= JHtml::_('select.option', '1', JText::_('Users_Disabled'));

		// Build the select list.
		$attr = 'class="inputbox" size="1" onchange="document.adminForm.submit();"';
		$html = JHtml::_('select.genericlist', $options, 'filter_state', $attr, 'value', 'text', $active);

		return $html;
	}

	function active($active = null)
	{
		// Build the filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '', JText::_('Users_Filter_Active'));
		$options[]	= JHtml::_('select.option', '0', JText::_('Users_Activated'));
		$options[]	= JHtml::_('select.option', '1', JText::_('Users_Unactivated'));

		// Build the select list.
		$attr = 'class="inputbox" size="1" onchange="document.adminForm.submit();"';
		$html = JHtml::_('select.genericlist', $options, 'filter_active', $attr, 'value', 'text', $active);

		return $html;
	}

	function usergroup($active = null)
	{
		$db = &JFactory::getDbo();
		$db->setQuery(
			'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN `#__usergroups` AS b ON a.left_id > b.left_id AND a.right_id < b.right_id' .
			' GROUP BY a.id' .
			' ORDER BY a.left_id ASC'
		);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		for ($i=0,$n=count($options); $i < $n; $i++) {
			$options[$i]->text = str_repeat('- ',$options[$i]->level).$options[$i]->text;
		}

		// If all usergroups is allowed, push it into the array.
		array_unshift($options, JHtml::_('select.option', '', JText::_('Users_Filter_Usergroup')));

		// Build the select list.
		$attr = 'class="inputbox" size="1" onchange="document.adminForm.submit();"';
		$html = JHtml::_('select.genericlist', $options, 'filter_group', $attr, 'value', 'text', $active);

		return $html;
	}
}