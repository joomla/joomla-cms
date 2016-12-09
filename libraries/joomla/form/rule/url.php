<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Uri\UriHelper;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  11.1
 */
class JFormRuleUrl extends JFormRule
{
	/**
	 * Method to test an external or internal url for all valid parts.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 * @link    http://www.w3.org/Addressing/URL/url-spec.txt
	 * @see	    JString
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		$urlParts = UriHelper::parse_url($value);

		// See http://www.w3.org/Addressing/URL/url-spec.txt
		// Use the full list or optionally specify a list of permitted schemes.
		if ($element['schemes'] == '')
		{
			$scheme = array('http', 'https', 'ftp', 'ftps', 'gopher', 'mailto', 'news', 'prospero', 'telnet', 'rlogin', 'sftp', 'tn3270', 'wais', 'url',
				'mid', 'cid', 'nntp', 'tel', 'urn', 'ldap', 'file', 'fax', 'modem', 'git');
		}
		else
		{
			$scheme = explode(',', $element['schemes']);
		}

		/*
		 * Note that parse_url() does not always parse accurately without a scheme,
		 * but at least the path should be set always. Note also that parse_url()
		 * returns False for seriously malformed URLs instead of an associative array.
		 * @see https://secure.php.net/manual/en/function.parse-url.php
		 */
		if ($urlParts === false or !array_key_exists('scheme', $urlParts))
		{
			/*
			 * The function parse_url() returned false (seriously malformed URL) or no scheme
			 * was found and the relative option is not set: in both cases the field is not valid.
			 */
			if ($urlParts === false or !$element['relative'])
			{
				return false;
			}
			// The best we can do for the rest is make sure that the path exists and is valid UTF-8.
			if (!array_key_exists('path', $urlParts) || !StringHelper::valid((string) $urlParts['path']))
			{
				return false;
			}
			// The internal URL seems to be good.
			return true;
		}

		// Scheme found, check all parts found.
		$urlScheme = (string) $urlParts['scheme'];
		$urlScheme = strtolower($urlScheme);

		if (in_array($urlScheme, $scheme) == false)
		{
			return false;
		}

		// For some schemes here must be two slashes.
		$scheme = array('http', 'https', 'ftp', 'ftps', 'gopher', 'wais', 'prospero', 'sftp', 'telnet', 'git');

		if (in_array($urlScheme, $scheme) && substr($value, strlen($urlScheme), 3) !== '://')
		{
			return false;
		}

		// The best we can do for the rest is make sure that the strings are valid UTF-8
		// and the port is an integer.
		if (array_key_exists('host', $urlParts) && !StringHelper::valid((string) $urlParts['host']))
		{
			return false;
		}

		if (array_key_exists('port', $urlParts) && !is_int((int) $urlParts['port']))
		{
			return false;
		}

		if (array_key_exists('path', $urlParts) && !StringHelper::valid((string) $urlParts['path']))
		{
			return false;
		}

		return true;
	}
}
