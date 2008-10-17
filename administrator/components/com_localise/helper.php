<?php
/**
* @version 1.5
* @package com_localise
* @author Ifan Evans
* @copyright Copyright (C) 2007 Ifan Evans. All rights reserved.
* @license GNU/GPL
* @bugs - please report to post@ffenest.co.uk
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
* Helper class for the Translations component
*/
class TranslationsHelper
{
	/**
	* Get Meta Info from language translation file content.
	* @param mixed 		The contents of the file using file() or get_file_contents().
	* @param array  	A blank array, strings will be returned by association
	* @param array  	Optional associative array of reference strings
	* @return array		The Meta Info in an array
	*/
	function getINIMeta( $content, &$strings, $ref_strings = null )
	{
		// convert a string to an array
		if (is_string($content)) {
			$content = explode("\n",$content,10);
		} else if (!is_array($content)) {
			$content = array();
		}

		// look for a Byte-Order-Marker at the start of the file
		$file['bom'] = 'UTF-8';
		if ($content) {
			$bom = strtolower(bin2hex(substr($content[0],0,4)));
			if ( $bom == '0000feff' ) {
				$file['bom'] = 'UTF-32 BE';
			} else if ( $bom == 'feff0000' ) {
				$file['bom'] = 'UTF-32 LE';
			} else if ( substr($bom,0,4) == 'feff' ) {
				$file['bom'] = 'UTF-16 BE';
			} else if ( substr($bom,0,4) == 'fffe' ) {
				$file['bom'] = 'UTF-16 LE';
			}
		}

		// parse the top line from one of these two formats
		//	# $Id: helper.php 10619 2008-08-06 21:54:36Z eddieajau $
		//	# version 1.5.0 2007-01-25 10:40:16 ~0 +0
		if (strpos($content[0],'.ini')) {
			$line = preg_replace('/^.*[.]ini[ ]+/','',$content[0]);
			list( $file['version'], $file['date'], $file['time'], $file['owner'], $file['complete'] ) = explode( ' ', $line . '   ', 6 );
			$file['headertype'] = 1;
		} else {
			$line = preg_replace('/^.*version/i','',$content[0]);
			$line = trim($line);
			list( $file['version'], $file['date'], $file['time'], $file['complete'] ) = explode( ' ', $line . '   ', 5 );
			$file['owner'] = '';
			$file['headertype'] = 2;
		}

		// tidy up the values
		$file['complete']	= preg_replace('/[^0-9]/', '', $file['complete']);
		$file['author'] 	= preg_replace('/^.*author[ ]+/i', '', trim($content[1],'# ') );
		$file['copyright'] 	= preg_replace('/^.*copyright[ ]+/i', '', trim($content[2],'# ') );
		$file['license'] 	= preg_replace('/^.*license[ ]+/i', '', trim($content[3],'# ') );

		// parse the strings in the file into an associative array
		$strings = array();
		foreach ($content as $line) {
			$line = trim($line);
			// 1: skip comments and blanks
			// 2: get the ucase key and value
			if ((empty($line))||($line{0}=='#')||($line{0}==';')) {
				continue;
			} else if (strpos($line,'=')) {
				list($key,$value) = explode('=',$line,2);
				$key = strtoupper($key);
				$strings[$key] = $value;
			}
		}

		// get the status compared to the ref strings
		$file = array_merge( $file, TranslationsHelper::getINIstatus( $ref_strings, $strings ) );

		// set a complete flag
		if ( ( $file['complete'] == $file['unchanged'] ) && ( $file['missing'] == 0 ) ) {
			$file['status'] = 100;
		}

		// return
		return $file;
	}

	/**
	* Get Meta Info from language translation file content.
	* @param array  	The reference strings in an associative array
	* @param array  	The language strings in an associative array
	* @return array		The Meta Info in an array
	*/
	function getINIstatus( $ref_strings, $strings )
	{
		// initialise
		$file = array();
		$file['changed'] 	= 0;
		$file['extra'] 		= 0;
		$file['missing'] 	= 0;
		$file['refstrings'] = count($ref_strings);
		$file['status']		= 0;
		$file['strings']	= count($strings);
		$file['unchanged'] 	= 0;

		// count changes
		if (!$file['strings']) {
			$file['missing'] = $file['refstrings'];
		} else if (!$file['refstrings']) {
			$file['extra'] = $file['strings'];
		} else {
			// count the changes
			$all_strings = array_merge($ref_strings,$strings);
			foreach($all_strings as $k=>$v){
				if (!isset($ref_strings[$k])) {
					$file['extra']++;
				} else if (!isset($strings[$k])) {
					$file['missing']++;
				} else if ($v!=$ref_strings[$k]) {
					$file['changed']++;
				} else {
					$file['unchanged']++;
				}
			}
		}

		// set status
		if ($file['changed'] == 0) {
			$file['status'] = 0;
		} else if ($file['strings'] == $file['changed']) {
			$file['status'] = 100;
		} else {
			$file['status'] = min(100,floor( ($file['changed']/$file['strings'])*100 ));
		}

		// return
		return $file;
	}

	/**
	* Get Meta Info from an XML language file (extends Joomla method to handle mixed/lower cases)
	* @param string $xmlFile	The file to parse including the path.
	* @return array				The Meta Info in an array
	*/
	function getXMLMeta( $xmlFile ) {

		$xmlData = array(
			'author' 		=> '',
			'authorEmail' 	=> '',
			'authorUrl'		=> '',
			'client'		=> '',
			'copyright'		=> '',
			'creationDate'	=> '',
			'date'			=> date('Y-m-d'),
			'description'	=> '',
			'license'		=> '',
			'name'			=> '',
			'tag'			=> '',
			'time'			=> date('H:m:i'),
			'version'		=> '',
		);

		// load the XML file and run some tests to ensure that it exists and is a metafile
		$xml = & JFactory::getXMLParser('Simple');
		if (is_file($xmlFile)) {
			if ( $xml->loadFile($xmlFile) ) {
				if ($xml->document->name() == 'metafile') {
				    // all the nodes in the XML file will come through as lowercase keys
				    // process the $xmlData array against the XML object tree
        			foreach ($xmlData as $k=>$v) {
					    $k_lc = strtolower($k);
						$element = & $xml->document->{$k}[0];
						if ($element) {
						    $xmlData[$k] = $element->data();
						} else {
                            $element = & $xml->document->{$k_lc}[0];
                            if ($element) {
                                $xmlData[$k] = $element->data();
                            } else {
                                $xmlData[$k] = $v;
                            }
                        }
					}
				}
			}
			// patch the date
			if ( (empty($xmlData['date'])) && (!empty($xmlData['creationdate'])) ) $xmlData['date'] = $xmlData['creationdate'];
		}

		// return
		return $xmlData;
	}

	/**
	* Transform a translation phrase.
	* @param string $s		The phrase to transform.
	* @param array $options	The configuration array for the component.
	* @return string		The transformed phrase
	*/
	function strtr($s,$options) {
		// backticks
		if ($options['backticks']>0) {
			$s = strtr($s,"'",'`');
		} else if ($options['backticks']<0) {
			$s = strtr($s,'`',"'");
		}
		// return
		return $s;
	}
}
?>