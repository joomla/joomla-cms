<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldEditors extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Editors';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		// compile list of the editors
		$query	= 'SELECT element AS value, name AS text'
				. ' FROM #__extensions'
				. ' WHERE folder = "editors"'
				. ' AND enabled = 1'
				. ' ORDER BY ordering, name';
		$db = & JFactory::getDbo();
		$db->setQuery($query);

		$options = $db->loadObjectList();
		$lang = JFactory::getLanguage();
		foreach ($options as $i=>$option) {
				$lang->load('plg_editors_'.$option->value, JPATH_ADMINISTRATOR, null, false, false)
			||	$lang->load('plg_editors_'.$option->value, JPATH_PLUGINS .'/editors/'.$option->value, null, false, false)
			||	$lang->load('plg_editors_'.$option->value, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
			||	$lang->load('plg_editors_'.$option->value, JPATH_PLUGINS .DS.$this->_type.DS.$this->_name, $lang->getDefault(), false, false);
			$options[$i]->text = JText::_($option->text);
		}

		// @todo: Check for an error msg.

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);

		return $options;
	}
}
