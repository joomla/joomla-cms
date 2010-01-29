<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldSpacer extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Spacer';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		return ' ';
	}
	
	/**
	 * Method to get the field label
	 *
	 * @return	string		The field label
	 */
	protected function _getLabel()
	{
		if((string)$this->_element->attributes()->hr=='true') {
			$this->labelText = "JFIELD_SPACER_LABEL";
		}
		return parent::_getLabel();
	}	
	
}