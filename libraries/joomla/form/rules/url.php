<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formrule');
jimport('joomla.utilities.string');


/**
 * Form Rule class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		11.1
 */
class JFormRuleUrl extends JFormRule
{
	/**
	 * Method to test an external url for a valid parts.
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
		$urlParts = JString::parse_url($value);
		// See http://www.w3.org/Addressing/URL/url-spec.txt	
		if ($element['schemes'] == ''){
			$scheme = array('http','https','ftp','sftp','gopher','mailto','news','prospero','telnet',
				'rlogin','tn3270','wais','url','mid','cid','nntp','tel','urn','ldap','file','fax','modem');
		} else {
			$scheme	= explode(",",$element['schemes']);
			
		}
		$urlScheme = (string) $urlParts['scheme'];
		$urlSceheme = strtolower($urlScheme);
		if (in_array($urlScheme,$scheme) == false){
			return false;
		}
		if (($urlScheme == 'http' || $urlScheme == 'https' || $urlScheme == 'ftp' ||
			$urlScheme == 'sftp' || $urlScheme == 'gopher' || $urlScheme == 'wais'
			|| $urlScheme == 'gopher' || $urlScheme == 'prospero' || $urlScheme == 'telnet' )
			&& ((substr($value,strlen($urlScheme),3)) !== '://')){
			return false;
		}
		if (!JString::valid((string) $urlParts['host'])){
			return false;
		}
		if (!is_int((string) $urlParts['port']) && (string) $urlParts['port'] != null ){
			return false;
		}
		if (!JString::valid((string) $urlParts['path'])){
			return false;
		}		
		return true;			
	}
}