<?php
/**
 * @version		$Id: text.php 12105 2009-06-16 11:46:51Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.field');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldTemplates extends JFormField {

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Template parametersets';
		/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$db = JFactory::getDBO();

		$query = 'SELECT * FROM #__menu_template '
			. 'WHERE client_id = 0 '
			. 'AND home = 0';
		$db->setQuery( $query );
		$data = $db->loadObjectList();

		$default = JHtml::_( 'select.option', 0, JText::_( 'JOPTION_USE_DEFAULT' ), 'id', 'description' );
        	array_unshift( $data, $default );

		$selected = $this->_getSelected();
		$html = JHTML::_( 'select.genericlist', $data, $this->inputName, 'class="inputbox" size="1"', 'id', 'description', $this->value );
		return $html;
	}

	private function _getSelected()
	{
		$id = JRequest::getVar('cid', array(0));
		$db = JFactory::getDBO();
		$query = 'SELECT `template_id` FROM `#__menu` '
			. 'WHERE id = '.(int)$id[0];
		$db->setQuery( $query );
		$result = $db->loadResult();
		return $result;
	}
}
