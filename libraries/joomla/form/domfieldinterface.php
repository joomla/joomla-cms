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
 * @since 3.7
 */
interface JFormDomfieldinterface
{

	/**
	 * Transforms the field into an XML element and appends it as child on the
	 * given parent.
	 *
	 * @param stdClass $field
	 * @param DOMElement $parent
	 * @param JForm $form
	 * @return DOMElement
	 *
	 * @since 3.7
	 */
	public function appendXMLFieldTag($field, DOMElement $parent, JForm $form);

	/**
	 * Returns the parameters of the field as an XML string which can be loaded
	 * into JForm.
	 *
	 * @return string
	 *
	 * @since 3.7
	 */
	public function getFormParameters();
}
