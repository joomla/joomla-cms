<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('bitfolge.vcard');

/**
 * Class needed to extend vcard class and to correct minor errors
 *
 * @pacakge Joomla
 * @subpackage	Contacts
 */
class JvCard extends vCard
{
	// needed to fix bug in vcard class
	function setName($family='', $first='', $additional='', $prefix='', $suffix='') {
		$this->properties["N"] 	= "$family;$first;$additional;$prefix;$suffix";
		$this->setFormattedName(trim("$prefix $first $additional $family $suffix"));
	}

	// needed to fix bug in vcard class
	function setAddress($postoffice='', $extended='', $street='', $city='', $region='', $zip='', $country='', $type='HOME;POSTAL') {
		// $type may be DOM | INTL | POSTAL | PARCEL | HOME | WORK or any combination of these: e.g. "WORK;PARCEL;POSTAL"
		$separator = ';';

		$key 		= 'ADR';
		if ($type != '') {
			$key	.= $separator . $type;
		}
		$key.= ';ENCODING=QUOTED-PRINTABLE';

		$return = encode($postoffice);
		$return .= $separator . encode($extended);
		$return .= $separator . encode($street);
		$return .= $separator . encode($city);
		$return .= $separator . encode($region);
		$return .= $separator . encode($zip);
		$return .= $separator . encode($country);

		$this->properties[$key] = $return;
	}

	// added ability to set filename
	function setFilename($filename) {
		$this->filename = $filename .'.vcf';
	}

	// added ability to set position/title
	function setTitle($title) {
		$title 	= trim($title);

		$this->properties['TITLE'] 	= $title;
	}

	// added ability to set organisation/company
	function setOrg($org) {
		$org 	= trim($org);

		$this->properties['ORG'] = $org;
	}

	function getVCard($sitename) {
		$text 	= 'BEGIN:VCARD';
		$text	.= "\r\n";
		$text 	.= 'VERSION:2.1';
		$text	.= "\r\n";

		foreach($this->properties as $key => $value) {
			$text	.= "$key:$value";
			$text	.= "\r\n";
		}
		$text	.= 'REV:'. date('Y-m-d') .'T'. date('H:i:s'). 'Z';
		$text	.= "\r\n";
		$text	.= 'MAILER: Joomla! vCard for '. $sitename;
		$text	.= "\r\n";
		$text	.= 'END:VCARD';
		$text	.= "\r\n";

		return $text;
	}
}