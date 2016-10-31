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
 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function appendXMLFieldTag($field, DOMElement $parent, JForm $form);

	/**
	 * Returns the parameters of the field as an XML string which can be loaded
	 * into JForm.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getFormParameters();
}
