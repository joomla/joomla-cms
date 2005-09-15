<?php
/**
* @version $Id: editor.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $mosConfig_editor;

if ( !defined( '_MOS_EDITOR_INCLUDED' ) ) {
	global $my;

	if ( $my && $editor = $my->params->get( 'editor' ) ) {
		$mosConfig_editor = $editor;
	}
	if ($mosConfig_editor == '') {
		$mosConfig_editor = 'none';
	}

	//$_MAMBOTS->loadBotGroup( 'editors' );
	$_MAMBOTS->loadBot( 'editors', $mosConfig_editor, 1 );
	$_MAMBOTS->loadBotGroup( 'editors-xtd' );

	function initEditor() {
		global $mainframe, $_MAMBOTS;

		if(!$mainframe->get('loadEditor')) {
			return false;
		}

		$results = $_MAMBOTS->trigger( 'onInitEditor' );
		foreach ($results as $result) {
		    if (trim($result)) {
			   echo $result;
			}
		}
	}

	function getEditorContents( $editorArea, $hiddenField, $return=0 ) {
		global $mainframe, $_MAMBOTS;

		$mainframe->set('loadEditor', true);

		$results = $_MAMBOTS->trigger( 'onGetEditorContents', array( $editorArea, $hiddenField ) );
		foreach ( $results as $result ) {
		    if ( trim( $result ) ) {
		    	if ( $return ) {
		    		return $result;
		    	} else {
			   	echo $result;
		    	}
			}
		}
	}

	// just present a textarea
	function editorArea( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut=1, $return=0 ) {
		global $mainframe, $_MAMBOTS;

		$mainframe->set('loadEditor', true);

		$results = $_MAMBOTS->trigger( 'onEditorArea', array( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut ) );
		foreach ( $results as $result ) {
		    if ( trim( $result ) ) {
		    	if ( $return ) {
		    		return $result;
		    	} else {
			   	echo $result;
		    	}
			}
		}
	}
	define( '_MOS_EDITOR_INCLUDED', 1 );
}
?>