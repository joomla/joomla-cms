<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Articles
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.database.query');

/**
 * Renders a author element
 *
 * @package 	Joomla
 * @subpackage	Articles
 * @since		1.5
 */
class JElementAuthor extends JElement
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Author';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$access	= &JFactory::getACL();
		$groups	= array();

		// Include user in groups that have access to edit their own articles.
		$return = $access->getAuthorisedUsergroups('com_content.article.edit_own', true);
		if (count($return)) {
			$groups = array_merge($groups, $return);
		}

		// Include user in groups that have access to edit other people's articles.
		$return = $access->getAuthorisedUsergroups('com_content.article.edit_article', true);
		if (count($return)) {
			$groups = array_merge($groups, $return);
		}

		// Include user in groups that have access to manage content.
		$return = $access->getAuthorisedUsergroups('com_content.manage', true);
		if (count($return)) {
			$groups = array_merge($groups, $return);
		}

		// Remove duplicate entries and serialize.
		JArrayHelper::toInteger($groups);
		$groups = implode(',', array_unique($groups));

		// Build the query to get the possible authors.
		$query = new JQuery();
		$query->select('u.id AS value');
		$query->select('u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__user_usergroup_map AS m ON m.user_id = u.id');
		$query->where('u.block = 0');
		$query->where('m.group_id IN ('.$groups.')');

		$db = &JFactory::getDbo();
		$db->setQuery($query->toString());

		$users = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return false;
		}

		return JHtml::_('select.genericlist', $users, $name, 'class="inputbox" size="1"', 'value', 'text', $value);
	}
}