<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

if (!defined( '_MOS_EDITOR_INCLUDED' )) {
	

	function initEditor() {
		global $mainframe;
		
		if ($mainframe->get( 'loadEditor' )) {
			$results = $mainframe->triggerEvent( 'onInitEditor' );
			foreach ($results as $result) {
				if (trim($result)) {
				   echo $result;
				}
			}
		}
	}
	
	function _loadEditor()
	{
		global $mainframe, $mosConfig_editor, $my;
		
		if($mainframe->get( 'loadEditor' )) {
			return;
		}

		if ($mosConfig_editor == '') {
			$mosConfig_editor = 'none';
		}

		// Per User Editor selection
		$editor = $mosConfig_editor;
		
		if(isset($my)) {
			$params = new mosParameters( $my->params );
			$editor = $params->get( 'editor', $mosConfig_editor );
		}

		JBotLoader::import( 'editors', $editor, 1 );
		JBotLoader::importGroup( 'editors-xtd' );
		
		$mainframe->set( 'loadEditor', true );
	}
	function getEditorContents( $editorArea, $hiddenField ) {
		global $mainframe;
		
		_loadEditor();

		$results = $mainframe->triggerEvent( 'onGetEditorContents', array( $editorArea, $hiddenField ) );
		foreach ($results as $result) {
			if (trim($result)) {
				echo $result;
			}
		}
	}
	// just present a textarea
	function editorArea( $name, $content, $hiddenField, $width, $height, $col, $row ) {
		global $mainframe, $my;

		_loadEditor();

		$results = $mainframe->triggerEvent( 'onEditorArea', array( $name, $content, $hiddenField, $width, $height, $col, $row ) );
		foreach ($results as $result) {
			if (trim($result)) {
				echo $result;
			}
		}
	}

	define( '_MOS_EDITOR_INCLUDED', 1 );
}
?>