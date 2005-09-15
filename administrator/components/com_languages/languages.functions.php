<?php
/**
 * Support functions for Language Manager
 * @version $Id: languages.functions.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Joomla
 * @subpackage Languages
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Languages
 * @subpackage Languages
 * @return array
 */
function findLanguageFiles() {
	// find site language files
	$siteFiles = array();
	$languages = mosLanguage::getKnownLanguages( 0 );
	foreach ($languages as $file=>$name) {
		$temp['value'] = $file;
		$temp['name'] = $name;
		$siteFiles[] = $temp;
	}

	// find admin language files
	$adminFiles = array();
	$languages = mosLanguage::getKnownLanguages( 1 );
	foreach ( $languages as $file=>$name ) {
		$temp['value'] = $file;
		$temp['name'] = $name;
		$adminFiles[] = $temp;
	}

	// find admin language files
	$installFiles = array();
	$languages = mosLanguage::getKnownLanguages( 2 );
	foreach ( $languages as $file=>$name ) {
		$temp['value'] = $file;
		$temp['name'] = $name;
		$installFiles[] = $temp;
	}

	return array( $siteFiles, $adminFiles, $installFiles );
}

/** Modifies and stores an language xml file
 */
function copy_language_xml( $copyFromDir, $copyFrom, $copyToDir, $name ) {
	mosFS::load( '@domit' );
	$xmlDoc = new DOMIT_Lite_Document();
	$xmlDoc->resolveErrors( true );
	if (!$xmlDoc->loadXML( $copyFromDir .$copyFrom. '.xml', false, true )) {
		return false;
	}
	$language = &$xmlDoc->documentElement;

	// Check that it's am installation file
	if ($language->getTagName() != 'mosinstall') {
		return false;
	}

	if( $language->getAttribute( 'type' ) == 'language' ) {
		$nameElement =& $language->getElementsByPath( 'name', 1 );
		$nameElement->setText( $name );

		$filesElement =& $language->getElementsByPath( 'files', 1);
		$fileNodes =& $filesElement->getElementsByPath( 'filename' );
		for( $i=0; $i<$fileNodes->getLength(); $i++ ) {
			$fileNode =& $fileNodes->item( $i );
			$filename = $fileNode->getText();
			$newFile = preg_replace( "#^{$copyFrom}#", $name, $filename );
			$fileNode->setText( $newFile );
		}

		$toFile = $copyToDir . $name . '.xml';
		if( !$xmlDoc->saveXML($toFile, true) ) {
			return false;
		}
	}
}

/**
 * Trawls selected php and html files for translation functions
 * @return array New variables, and unused variables
 */
function trawlLanguages( $options ) {
	global $mosConfig_absolute_path;
	$client = mosGetParam( $options, 'client', 'all' );

	$ENGLISH = new mosLanguage;
	$exclude = '';
	switch ($client) {
		case '0':
			$path = mosFS::getNativePath( $mosConfig_absolute_path );
			$exclude = 'administrator|installation';
			$ENGLISH->load( '', 0 );
			break;
		case '1':
			$path = mosFS::getNativePath( $mosConfig_absolute_path . '/administrator' );
			echo $ENGLISH->load( '', 0 );
			echo $ENGLISH->load( '', 1 );
			break;
		case '2':
			$path = mosFS::getNativePath( $mosConfig_absolute_path . '/installation' );
			$ENGLISH->load( '', 2 );
			break;
		default:
			$path = mosFS::getNativePath( $mosConfig_absolute_path );
			break;
	}

	$foundkeys = array();
	$com_foundkeys = array();
	$com_keys = array();
	$files = mosFS::listFiles( $path, "\.php$|\.html$|\.xml", true, true );

	foreach ($files as $file) {
		$file = mosFS::getNativePath( $file, false );
		if ($exclude && eregi( $exclude, $file )) {
			continue;
		}
		$buffer = file_get_contents( $file );
		$m = null;
		$info = pathinfo( $file );
		$isHtml = false;
		$isXML = false;
		if ($info['extension'] == 'php') {
			$rx = "/_LANG->_\(\s*[\'\"]([^\'\"]*)[\'\"]\s*\)[\.:;\b\s]/is";
		} else if ($info['extension'] == 'html') {
			$rx = "#<mos:Translate(\s*key=\"([^\"]*)\")?>(.*?)</mos:Translate>#is";
			$isHtml = true;
		} else if ($info['extension'] == 'xml') {
			$rx = "#(label=\"([^\"]*)\")|(description=\"([^\"]*)\")#is";
			$isXML = true;
		} else {
			$rx = '';
		}
		preg_match_all( $rx, $buffer, $matches, PREG_SET_ORDER  );

		if (count( $matches ) < 1) {
			continue;
		}
		//$matches = array_merge( $m[1] );
		//$matches = array_unique( $matches );

		if (eregi( 'com_|installation', $file )) {
			$COM = new mosLanguage();

			if (!@$com_keys[$file]) {
				$com_keys[$file] = array();
			}
			$sep = addslashes( DIRECTORY_SEPARATOR );
			$regex = '#(com_[^' . $sep . ']*)' . $sep . '#';
			if (preg_match( $regex, $file, $regs )) {
				if (eregi( 'administrator', $file )) {
					$dir = $mosConfig_absolute_path . '/administrator/language/';
				} else {
					$dir = $mosConfig_absolute_path . '/language/';
				}
				$COM->load( $regs[1], $client );
			}

			foreach ($matches as $v) {
				if ($isHtml) {
					if ($v[2]) {
						$key = $v[2];
					} else {
						$key = $v[3];
					}
					$value = $v[3];
				} else if ($isXML) {
					//echo '<div>';print_r($v);echo '</div>';
					if ($v[1]) {
						$key = $v[2];
						$value = $v[2];
					} else if ($v[3]) {
						$key = $v[4];
						$value = $v[4];
					}
				} else {
					$key = $v[1];
					$value = $v[1];
				}
				if (!preg_match( "/{.*?}/", $key )) {
					//echo "<br>$key " . $COM->hasKey( $key ) . ', ' . $ENGLISH->hasKey( $key );
					if ($COM->hasKey( $key ) || $ENGLISH->hasKey( $key )) {
						$com_foundkeys[strtoupper( $key )] = $value;
					} else {
						$com_keys[$file][strtoupper( $key )] = $value;
					}
				}
			}
		} else {
			foreach ($matches as $v) {
				if ($isHtml) {
					if ($v[2]) {
						$key = $v[2];
					} else {
						$key = $v[3];
					}
					$value = $v[3];
				} else {
					$key = $v[1];
					$value = $v[1];
				}
				if (!preg_match( "/{.*?}/", $key )) {
					$foundkeys[strtoupper( $key )] = $value;
				}
			}
		}
	}

	//echo '<div align=left><pre>';print_r($foundkeys);echo '</pre></div>';
	//echo '<div align=left><pre>';print_r($com_keys);echo '</pre></div>';
	$foundkeys = array_change_key_case( $foundkeys, CASE_UPPER );
	$com_foundkeys = array_change_key_case( $com_foundkeys, CASE_UPPER );
	ksort( $foundkeys );
	$langkeys = array_change_key_case( $ENGLISH->_strings, CASE_UPPER );
	$diff = array_diff( array_keys( $foundkeys ), array_keys( $langkeys ) );
	$diff = array_unique( $diff );
	//echo '<div align=left>LANGKEYS<pre>';print_r($langkeys);echo '</pre></div>';
	//echo '<div align=left>FOUNDKEYS<pre>';print_r($foundkeys);echo '</pre></div>';
	//echo '<div align=left>DIFF<pre>';print_r($diff);echo '</pre></div>';

	$basePath = mosPathName( $mosConfig_absolute_path, false );

	// Do just the differences
	$vars = '';
	asort( $diff );
	$vars .= "[Common]\n";
	foreach ($diff as $key) {
		if (trim( $key )) {
			$vars .= $key . '=' . $foundkeys[$key] . "\n";
		}
	}

	// do the components
	//echo '<div align=left>COM KEYS<pre>';print_r($com_keys);echo '</pre></div>';
	foreach ($com_keys as $com=>$keys) {
		//echo "\n".$com;
		$diff = array_diff( array_keys( $keys ), array_keys( $langkeys ) );
		$diff = array_unique( $diff );
		$foundkeys = array_merge( $foundkeys, $keys );

		//echo '<div align=left><pre>';print_r($langkeys);echo '</pre></div>';
		//echo '<div align=left><pre>';print_r($keys);echo '</pre></div>';
		//echo '<div align=left><pre>';print_r($diff);echo '</pre></div>';
		asort( $diff );
		if (count( $diff)) {
			$name = str_replace( $basePath, '', $com );
			$temp = '';
			foreach ($diff as $key) {
				if (trim( $key )) {
					$keys[$key] = str_replace( "\r", '', $keys[$key] );
					$temp .= $key . '=' . str_replace( "\n", '\n', $keys[$key] ) . "\n";
				}
			}
			if (trim( $temp ) != '') {
				$vars .= "\n# [" . $name . "]\n\n" . $temp;
			}
		}
	}

	// explore keys that are unset or not yet converted
	//echo '<div align=left>FOUND<pre>';print_r($foundkeys);echo '</pre></div>';
	$unset = array_diff( array_keys( $langkeys ), array_keys( array_merge( $foundkeys, $com_foundkeys ) ) );
	$unset = array_unique( $unset );

	$uvars = '';
	asort( $unset );
	$uvars .= "# Common\n\n";
	foreach ($unset as $key) {
		if (trim( $key )) {
			$guess = ucfirst( strtolower( $key ) );
			//$guess = str_replace( '_', ' ', $guess );
			$uvars .= $key . '=' . $langkeys[$key] . "\n";
		}
	}
	return array( $vars, $uvars );
}
?>