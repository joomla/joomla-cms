<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.extension.plugin' );

/**
 * No WYSIWYG Editor Plugin
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @package Editors
 * @since 1.5
 */
class JEditor_none extends JPlugin {

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @since 1.5
	 */
	function JEditor_none(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * Method to handle the onInitEditor event.
	 *  - Initializes the Editor
	 *
	 * @access public
	 * @return string JavaScript Initialization string
	 * @since 1.5
	 */
	function onInit() {
		$txt =	"<script type=\"text/javascript\">
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
				</script>";
		return $txt;
	}

	/**
	 * No WYSIWYG Editor - copy editor content to form field
	 *
	 * @param string 	The name of the editor
	 */
	function onSave( $editor ) {
		return;
	}

	/**
	 * No WYSIWYG Editor - get the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onGetContent( $editor ) {
		return;
	}

	/**
	 * No WYSIWYG Editor - set the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onSetContent( $editor, $html ) {
		return;
	}

	/**
	 * No WYSIWYG Editor - display the editor
	 *
	 * @param string The name of the editor area
	 * @param string The content of the field
	 * @param string The name of the form field
	 * @param string The width of the editor area
	 * @param string The height of the editor area
	 * @param int The number of columns for the editor area
	 * @param int The number of rows for the editor area
	 */
	function onDisplay( $name, $content, $width, $height, $col, $row ) {
		$width  = $width . 'px';
		$height = $height . 'px';
		
		return "<textarea name=\"$name\" id=\"$name\" cols=\"$col\" rows=\"$row\" style=\"width: $width; height: $height;\">$content</textarea>";
	}

	function onGetInsertMethod($name) {
		global $mainframe;

		$doc = & $mainframe->getDocument();

		$js= "\tfunction jInsertEditorText( text ) {
			insertAtCursor( document.adminForm.".$name.", text );
		}";
		$doc->addScriptDeclaration($js);

		return true;
	}
}
?>