<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Supports a URL text field
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.url.html#input.url
 * @see         JFormRuleUrl for validation of full urls
 * @since       11.1
 */
class JFormFieldUrl extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Url';

	/**
	* Method to get the field input markup.
	* When used in conjunction with the url filter in JFormField, if the relative element is
	* false the method assumes most URLS are external.
	* When relative is true a URL that does not include a protocol is assumed to be local.
	* This method does not validate a URL which should be done using JFormRuleUrl.
	*
	* @return  string
	*
	* @see     JFormRuleUrl, JForm::Filter
	* @since   11.1
	*/
	protected function getInput()
	{
		protected $type = 'Url';
	}
}
