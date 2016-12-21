<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die();

/**
 * Interface which marks a JFormField as available for com_fields.
 *
 * @since  3.7.0
 */
interface JFormDomfieldinterface
{
	/**
	 * Function to manipulate the DOM element of the field. The form can be
	 * manipulated at that point.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   JForm       $form    The form.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function appendXMLFieldTag($field, DOMElement $parent, JForm $form);
}
