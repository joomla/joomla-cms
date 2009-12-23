<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.field');

/**
 * Impressions Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	com_banners
 * @since		1.6
 */
class JFormFieldImpTotal extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'ImpTotal';

	protected function _getInput()
	{
		$class		= ' class="validate-numeric text_area"';
		$onchange	= ' onchange="document.id(\''.$this->inputId.'_unlimited\').checked=document.id(\''.$this->inputId.'\').value==\'\';"';
		$onclick	= ' onclick="if (document.id(\''.$this->inputId.'_unlimited\').checked) document.id(\''.$this->inputId.'\').value=\'\';"';
		$value		= empty($this->value) ? '' : $this->value;
		$checked	= empty($this->value) ? ' checked="checked"' : '';

		return '<input type="text" name="'.$this->inputName.'" id="'.$this->inputId.'" value="'.htmlspecialchars($value).'" '.$class.$onchange.' /><input id="'.$this->inputId.'_unlimited" type="checkbox"'.$checked.$onclick.' /><input style="border:0;" type="text" value="'.JText::_('Banners_Unlimited').'" readonly="readonly"/>';
	}
}
