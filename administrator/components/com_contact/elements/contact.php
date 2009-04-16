<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @since		1.6
 */
class JElementContact extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Contact';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT DISTINCT c.id, c.name AS text'
		. ' FROM #__contact_contacts AS c'
		. ' LEFT JOIN #__contact_con_cat_map AS map ON map.contact_id = c.id '
		. ' LEFT JOIN #__categories AS cat ON cat.id = map.category_id '
		. ' WHERE c.published = 1 AND cat.published = 1'
		. ' ORDER BY cat.title, c.name';
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return JHtml::_('select.genericlist', $options, $control_name.'['.$name.']',
			array(
				'id' => $control_name.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value,
				'option.key' => 'id',
				'option.text' => 'text'
			)
		);
	}
}