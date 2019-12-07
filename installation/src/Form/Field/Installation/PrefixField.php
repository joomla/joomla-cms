<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Form\Field\Installation;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Installation\Helper\DatabaseHelper;

/**
 * Database Prefix field.
 *
 * @since  1.6
 */
class PrefixField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Prefix';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$size      = $this->element['size'] ? abs((int) $this->element['size']) : 5;
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class     = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly  = (string) $this->element['readonly'] === 'true' ? ' readonly="readonly"' : '';
		$disabled  = (string) $this->element['disabled'] === 'true' ? ' disabled="disabled"' : '';

		// Make sure somebody doesn't put in a too large prefix size value.
		if ($size > 15)
		{
			$size = 15;
		}

		// If a prefix is already set, use it instead.
		$session = Factory::getSession()->get('setup.options', array());

		if (empty($session['db_prefix']))
		{
			$prefix = DatabaseHelper::getPrefix();
		}
		else
		{
			$prefix = $session['db_prefix'];
		}

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' .
				' value="' . htmlspecialchars($prefix, ENT_COMPAT, 'UTF-8') . '"' .
				$class . $disabled . $readonly . $onchange . $maxLength . '>';
	}
}
