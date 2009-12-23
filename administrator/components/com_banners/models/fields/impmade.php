<?php
/**
 * @version		$Id: media.php 12774 2009-09-18 04:47:09Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.field');

/**
 * Clicks Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	com_banners
 * @since		1.6
 */
class JFormFieldImpMade extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'ImpMade';

	protected function _getInput()
	{
		$onclick	= ' onclick="document.id(\''.$this->inputId.'\').value=\'0\';"';

		return '<input style="border:0;" type="text" name="'.$this->inputName.'" id="'.$this->inputId.'" value="'.htmlspecialchars($this->value).'" readonly="readonly" /><input type="button"'.$onclick.' value="'.JText::_('Banners_Reset_ImpMade').'" class="button"/>';
	}
}
