<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Utility class for com_config
 *
 * @static
 * @package 	Joomla
 * @subpackage	Config
 * @since		1.6
 */

class JHtmlFilter
{
	function assigned($client, $selected = null)
	{
		$db		= &JFactory::getDbo();
		// template assignment filter
		$query = 'SELECT CONCAT(template," - ",description) AS text, id AS value'.
				' FROM #__menu_template' .
				' WHERE client_id = '.(int) $client->id;
		$db->setQuery($query);
		$assigned[]		= JHtml::_('select.option',  '0', '- '. JText::_('Select Template') .' -');
		$assigned 		= array_merge($assigned, $db->loadObjectList());
		return JHtml::_(
			'select.genericlist',
			$assigned,
			'filter_assigned',
			array(
				'list.attr' => 'class="inputbox" size="1" onchange="this.form.submit()"',
				'list.select' => $selected
			)
		);
	}

	function position($client, $selected = null)
	{
		$db		= &JFactory::getDbo();

		// get list of Positions for dropdown filter
		$query = 'SELECT m.position AS value, m.position AS text'
		. ' FROM #__modules as m'
		. ' WHERE m.client_id = '.(int) $client->id
		. ' GROUP BY m.position'
		. ' ORDER BY m.position'
		;
		$positions[] = JHtml::_('select.option',  '0', '- '. JText::_('Select Position') .' -');
		$db->setQuery($query);
		$positions = array_merge($positions, $db->loadObjectList());
		return JHtml::_(
			'select.genericlist',
			$positions,
			'filter_position',
			array(
				'list.attr' => 'class="inputbox" size="1" onchange="this.form.submit()"',
				'list.select' => $selected
			)
		);
	}

	function type($client, $selected = null)
	{
		$db		= &JFactory::getDbo();

		// get list of Positions for dropdown filter
		$query = 'SELECT module AS value, module AS text'
		. ' FROM #__modules'
		. ' WHERE client_id = '.(int) $client->id
		. ' GROUP BY module'
		. ' ORDER BY module'
		;
		$db->setQuery($query);
		$types[] 		= JHtml::_('select.option',  '0', '- '. JText::_('Select Type') .' -');
		$types 			= array_merge($types, $db->loadObjectList());
		return JHtml::_(
			'select.genericlist',
			$types,
			'filter_type',
			array(
				'list.attr' => 'class="inputbox" size="1" onchange="this.form.submit()"',
				'list.select' => $selected
			)
		);
	}
}
