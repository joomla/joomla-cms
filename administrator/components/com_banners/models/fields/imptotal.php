<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Impressions Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class JFormFieldImpTotal extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ImpTotal';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$class		= ' class="validate-numeric text_area"';
		$onchange	= ' onchange="document.id(\''.$this->id.'_unlimited\').checked=document.id(\''.$this->id.'\').value==\'\';"';
		$onclick	= ' onclick="if (document.id(\''.$this->id.'_unlimited\').checked) document.id(\''.$this->id.'\').value=\'\';"';
		$value		= empty($this->value) ? '' : $this->value;
		$checked	= empty($this->value) ? ' checked="checked"' : '';

		return '<input type="text" name="'.$this->name.'" id="'.$this->id.'" size="9" value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" '.$class.$onchange.' />
		<fieldset class="checkboxes impunlimited"><input id="'.$this->id.'_unlimited" type="checkbox"'.$checked.$onclick.' />
		<label for="'.$this->id.'_unlimited" id="jform-imp" type="text">'.JText::_('COM_BANNERS_UNLIMITED').'</label></fieldset>';
	}
}
