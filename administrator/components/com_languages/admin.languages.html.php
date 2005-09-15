<?php
/**
* @version $Id: admin.languages.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
 * @package Joomla
 * @subpackage Languages
 */
class languageScreens {
	/**
	 * Static method to create the template object
	 * @param int The client identifier
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $client=0, $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );
		$tmpl->addVar( 'body', 'client', $client );

		return $tmpl;
	}

	/**
	 * Adds the tree variables to the template
	 * @param object patTemplate object
	 * @param array Language list
	 */
	function addTree( &$tmpl, &$tree ) {
		// set up the tree
		if (count( $tree[0] )) {
			$tmpl->addRows( 'site-files', $tree[0] );
		}
		if (count( $tree[1] )) {
			$tmpl->addRows( 'admin-files', $tree[1] );
		}
		if (count( $tree[2] )) {
			$tmpl->addRows( 'install-files', $tree[2] );
		}
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function installOptions( &$tree ) {
		$tmpl =& languageScreens::createTemplate( 0, array( 'installer.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'installOptions.html' );

		$tmpl->addVar( 'body', 'sitepath', $GLOBALS['mosConfig_absolute_path'] );
		languageScreens::addTree( $tmpl, $tree );

		$tmpl->displayParsedTemplate( 'form' );
	}
	/**
	 * Finished install
	 */
	function installDone( &$tree, $element, $errno, $error ) {
		$tmpl =& languageScreens::createTemplate( 0 );
		$tmpl->setAttribute( 'body', 'src', 'installDone.html' );

		$tmpl->addVar( 'body', 'element', $element );
		$tmpl->addVar( 'body', 'errno', $errno );
		$tmpl->addVar( 'body', 'message', $error );
		languageScreens::addTree( $tmpl, $tree );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function packageOptions( &$tree, $vars, $element, $client ) {
		$tmpl =& languageScreens::createTemplate( $client, array( 'installer.html', 'xml.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'packageOptions.html' );

		$fileName = mosMainFrame::getClientName( $client ) . 'Language_' . preg_replace( '#\.xml$#', '', $element );
		$tmpl->addVar( 'body', 'filename', $fileName );
		$tmpl->addVar( 'body', 'element', $element );
		if (isset( $vars['meta'] )) {
			$tmpl->addVars( 'body', $vars['meta'], 'meta_' );
		}
		if (isset( $vars['siteFiles'] )) {
			$tmpl->addRows( 'xml-site-files', $vars['siteFiles'] );
		}
		languageScreens::addTree( $tmpl, $tree );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Lists package files
	 * @param array An array of files
	 */
	function listFiles( &$tree, &$files ) {
		$tmpl =& languageScreens::createTemplate( 0, array( 'files.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'listFiles.html' );

		// set up the tree
		$tmpl->addRows( 'file-list-rows', $files );
		languageScreens::addTree( $tmpl, $tree );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Show the edit XML form
	 * @param array An array of xml variables
	 * @param string The language name
	 * param int The client identifier
	 */
  	function editXML( &$vars, $element, $client ) {
	   $tmpl =& languageScreens::createTemplate( $client, array( 'xml.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'editxml.html' );
		$tmpl->readTemplatesFromInput( 'common.html' );

		if (isset( $vars['meta'] )) {
			$tmpl->addVars( 'body', $vars['meta'], 'meta_' );
		}

		if (isset( $vars['siteFiles'] )) {
			$tmpl->addRows( 'site-files-list', $vars['siteFiles'] );
		}
		$tmpl->addVar( 'body', 'element', $element );
		$tmpl->addVar( 'body', 'client', $client );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Displays a message
	 * @param string The text to display
	 */
	function message( $text ) {
		$tmpl =& languageScreens::createTemplate( 0 );

		$tmpl->readTemplatesFromInput( 'common.html' );
		$tmpl->addVar( 'mosmsg', 'mosmsg', $text );
		$tmpl->displayParsedTemplate( 'message' );
	}

	/**
	* List languages
	* @param array
	*/
	function listLangs( &$tree, &$rows, $xmlFile, $vars, $element, $client ) {
		global $mosConfig_lang;

		$tmpl =& languageScreens::createTemplate( $client );

		$tmpl->setAttribute( 'body', 'src', 'listLangs.html' );

		// set up the tree
		if (count( $tree[0] )) {
			$tmpl->addRows( 'site-files', $tree[0] );
			if ($client == 0) {
				$tmpl->addRows( 'copy-files', $tree[0] );
			}
		}
		if (count( $tree[1] )) {
			$tmpl->addRows( 'admin-files', $tree[1] );
			if ($client == 1) {
				$tmpl->addRows( 'copy-files', $tree[1] );
			}
		}
		if (count( $tree[2] )) {
			$tmpl->addRows( 'install-files', $tree[2] );
			if ($client == 2) {
				$tmpl->addRows( 'copy-files', $tree[2] );
			}
		}
		if (!is_null( $xmlFile )) {
			$tmpl->addVars( 'xml-meta', $xmlFile['meta'] );
		}

		// set up the list
		$tmpl->addRows( 'body-list-rows', $rows );
		$tmpl->addVar( 'body', 'element', $element );
		$tmpl->addVars( 'body', $vars );

		if ($element != 'english' && $element != $mosConfig_lang) {
			$tmpl->addVar( 'body', 'canDelete', $client );
		}

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	* Edit language
	* @param array
	*/
	function edit( &$rows, &$vars, $element, $client, $file ) {
		$tmpl =& languageScreens::createTemplate( $client );

		$tmpl->setAttribute( 'body', 'src', 'edit.html' );
		//$tmpl->addVar( 'body', 'element', $element );
		$tmpl->addRows( 'body-list-rows', $rows );

		$tmpl->addVars( 'body', $vars, 'var_' );

		if (isset( $vars['rtl'] )) {
			$checked = array(
				'rtl_0' => $vars['rtl'] == 0 ? 'checked="true"' : '',
				'rtl_1' => $vars['rtl'] == 1 ? 'checked="true"' : ''
			);
			$tmpl->addVars( 'body', $checked, 'checked_' );
		}
		$tmpl->addVar( 'body', 'element', $element );
		$tmpl->addVar( 'body', 'file', $file );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Displays the option for trawling
	 */
    function trawlOptions( &$tree ) {
	   // import the body of the page
	   $tmpl =& languageScreens::createTemplate( 0 );
	   $tmpl->setAttribute( 'body', 'src', 'trawlOptions.html' );
		languageScreens::addTree( $tmpl, $tree );
	   $tmpl->displayParsedTemplate( 'form' );
    }
	/**
	 * Displays new langugae constants
	 * @param string
	 * @param string
	 */
    function trawl( &$tree, $buffers, $options ) {
	   // import the body of the page
	   $tmpl =& languageScreens::createTemplate( 0 );
	   $tmpl->setAttribute( 'body', 'src', 'trawl.html' );

	   $tmpl->addVar( 'body', 'buffer1', $buffers[0] );
	   $tmpl->addVar( 'body', 'buffer2', $buffers[1] );
	   //$tmpl->addVar( 'body', 'buffer3', $buffers[2] );
	   $tmpl->addVars( 'body', $options );
		languageScreens::addTree( $tmpl, $tree );

	   $tmpl->displayParsedTemplate( 'form' );
    }
}
?>