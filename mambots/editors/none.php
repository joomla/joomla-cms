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
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onInitEditor', 'botNoEditorInit' );
$mainframe->registerEvent( 'onGetEditorContents', 'botNoEditorGetContents' );
$mainframe->registerEvent( 'onEditorArea', 'botNoEditorEditorArea' );

/**
* No WYSIWYG Editor - javascript initialisation
*/
function botNoEditorInit() {
	return <<<EOD
<script type="text/javascript">
	function insertAtCursor(myField, myValue) {
		if (document.selection) {
			// IE support
			myField.focus();
			sel = document.selection.createRange();
			sel.text = myValue;
		} else if (myField.selectionStart || myField.selectionStart == '0') {
			// MOZILLA/NETSCAPE support
			var startPos = myField.selectionStart;
			var endPos = myField.selectionEnd;
			myField.value = myField.value.substring(0, startPos)
				+ myValue
				+ myField.value.substring(endPos, myField.value.length);
		} else {
			myField.value += myValue;
		}
	}
</script>
EOD;
}
/**
* No WYSIWYG Editor - copy editor contents to form field
* @param string The name of the editor area
* @param string The name of the form field
*/
function botNoEditorGetContents( $editorArea, $hiddenField ) {
	return <<<EOD
EOD;
}
/**
* No WYSIWYG Editor - display the editor
* @param string The name of the editor area
* @param string The content of the field
* @param string The name of the form field
* @param string The width of the editor area
* @param string The height of the editor area
* @param int The number of columns for the editor area
* @param int The number of rows for the editor area
*/
function botNoEditorEditorArea( $name, $content, $hiddenField, $width, $height, $col, $row ) {
	global $mainframe;

	$results = $mainframe->triggerEvent( 'onCustomEditorButton' );
	$buttons = array();
	foreach ($results as $result) {
		if ( $result[0] ) {
			$buttons[] = '<img src="'.JURL_SITE.'/mambots/editors-xtd/'.$result[0].'" onclick="insertAtCursor( document.adminForm.'.$hiddenField.', \''.$result[1].'\' )" alt="'.$result[1].'"/>';
		}
	}
	$buttons = implode( "", $buttons );

	return <<<EOD
<textarea name="$hiddenField" id="$hiddenField" cols="$col" rows="$row" style="width:$width;height:$height;">$content</textarea>
<br />$buttons
EOD;
}
?>