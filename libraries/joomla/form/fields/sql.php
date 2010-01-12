<?php

/**
 * @version		$Id: category.php 13825 2009-12-23 01:03:06Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

// Import html library
jimport('joomla.html.html');

// Import joomla field list class
require_once dirname(__FILE__) . DS . 'list.php';

/**
 * Supports an SQL select list of menu
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldSQL extends JFormFieldList
{

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'SQL';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions() 
	{
		$db = JFactory::getDbo();
		$db->setQuery($this->_element->attributes('query'));
		$key = ($this->_element->attributes('key_field') ? $this->_element->attributes('key_field') : 'value');
		$value = ($this->_element->attributes('value_field') ? $this->_element->attributes('value_field') : $this->name);
		$items = $db->loadObjectlist();

		// Check for an error.
		if ($db->getErrorNum()) 
		{
			JError::raiseWarning(500, $db->getErrorMsg());
			return false;
		}

		// Prepare return value
		$options = array();
		if (!empty($items)) 
		{

			// Iterate over items
			foreach($items as $item) 
			{
				$options[] = JHtml::_('select.option', $item->$key, $item->$value);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);
		return $options;
	}
}

