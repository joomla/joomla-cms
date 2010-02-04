<?php

/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');
require_once dirname(__FILE__) . DS . 'groupedlist.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldTemplateStyle extends JFormFieldGroupedList
{

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'TemplateStyle';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getGroups()
	{
		$client = $this->_element->attributes('client');
		$client_id = ($client == 'administrator') ? 1 : 0;
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->select($db->nameQuote('id'));
		$query->select($db->nameQuote('title'));
		$query->select($db->nameQuote('template'));
		$query->from($db->nameQuote('#__template_styles'));
		$query->where($db->nameQuote('client_id') . '=' . (int)$client_id);
		$query->order($db->nameQuote('template'));
		$query->order($db->nameQuote('title'));
		$db->setQuery($query);
		$styles = $db->loadObjectList();

		// Pre-process into groups.
		$last = null;
		$groups = array();
		foreach($styles as $style) {
			if ($style->template != $last) {
				$last = $style->template;
				$groups[$last] = array();
			}
			$groups[$last][] = JHtml::_('select.option', $style->id, $style->title);
		}

		// Merge any additional options in the XML definition.
		$groups = array_merge(parent::_getGroups(), $groups);
		return $groups;
	}
}