<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Form Field Search class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class JFormFieldSearch extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Search';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$html  = '';
		$html .= '<div class="btn-group pull-left">';
		$html .= '<input type="text" name="' . $this->name . '" id="' . $this->id . '" placeholder="' . JText::_('JSEARCH_FILTER') . '" value="' . htmlspecialchars($this->value) . '" title="' . JText::_('JSEARCH_FILTER') . '" onchange="this.form.submit();" />';
		$html .= '</div>';
		$html .= '<div class="btn-group">';
		$html .= '<button type="submit" class="btn tip" title="' . JText::_('JSEARCH_FILTER_SUBMIT') . '"><i class="icon-search"></i></button>';
		$html .= '<button type="button" class="btn tip" title="' . JText::_('JSEARCH_FILTER_CLEAR') . '" onclick="document.id(\'' . $this->id . '\').value=\'\';this.form.submit();"><i class="icon-remove"></i></button>';
		$html .= '</div>';
		return $html;
	}
}
