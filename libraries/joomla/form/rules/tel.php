<?php
/**
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 */

defined('JOOMLA_PLATFORM') or die;

jimport('joomla.form.formrule');


/**
 * Form Rule class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		11.1
 */
class JFormRuleTel extends JFormRule
{
	/**
	 * Method to test the url for a valid parts.
	 *
	 * @param	object	$element	The JXMLElement object representing the <field /> tag for the
	 * 								form field object.
	 * @param	mixed	$value		The form field value to validate.
	 * @param	string	$group		The field name group control value. This acts as as an array
	 * 								container for the field. For example if the field has name="foo"
	 * 								and the group value is set to "bar" then the full field name
	 * 								would end up being "bar[foo]".
	 * @param	object	$input		An optional JRegistry object with the entire data set to validate
	 * 								against the entire form.
	 * @param	object	$form		The form object for which the field is being tested.
	 *
	 * @return	boolean	True if the value is valid, false otherwise.
	 * @since	11.1
	 * @throws	JException on invalid rule.
	 */
	public function test(& $element, $value, $group = null, & $input = null, & $form = null)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');
		if (!$required && empty($value)) {
			return true;
		}		
		//See http://www.nanpa.com/
		//http://tools.ietf.org/html/rfc4933
		//http://www.itu.int/rec/T-REC-E.164/en
		 
		//Regex by Steve Levithan
		//see http://blog.stevenlevithan.com/archives/validate-phone-number
		$regexarray = array(
			NANP => '/^(?:\+?1[-. ]?)?\(?([2-9][0-8][0-9])\)?[-. ]?([2-9][0-9]{2})[-. ]?([0-9]{4})$/',
			ITU-T=> '/^\+(?:[0-9] ?){6,14}[0-9]$/',
			EPP => '/^\+[0-9]{1,3}\.[0-9]{4,14}(?:x.+)?$/'
		);
		if ($plan =='northamerica' || $plan == 'us' ) {
			$plan = 'NANP';
		} else
		 if ( $plan == 'International' || $plan == 'int' || $plan == 'missdn' || !$plan) {
		 	$plan = 'ITU-T';
		 } else 
		 if ( $plan == 'IETF') {
		 	$plan='EPP';
		 }
		$plan = (string) $element['plan'];
		$regex = (string) $regexarray[$plan];
		// Test the value against the regular expression.
		if (preg_match($regex, $value) == false) {
			return false;
		}			
		return true;	
	}
}	
