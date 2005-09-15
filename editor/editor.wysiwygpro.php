<?php
/**
* @version $Id: editor.wysiwygpro.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );
?>
<script type="text/javascript">
<!--
/** Wrapper around the editor specific update function in JavaScript
*/
function updateEditorContents( editorName, newValue ) {
	wp_send_to_html( 'editorName' );
}
//-->
</script>
<?php
function initEditor() {
	global $mosConfig_live_site, $mainframe;

	if(!$mainframe->get('loadEditor')) {
		return false;
	}
}

function editorArea( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut=1 ) {
	global $mosConfig_absolute_path, $mainframe;

	$mainframe->set('loadEditor', true);

	$content = str_replace('&lt;', '<', $content);
	$content = str_replace('&gt;', '>', $content);
	$content = str_replace('&amp;', '&', $content);
	$content = str_replace('&nbsp;', ' ', $content);
	$content = str_replace('&quot;', '\'', $content);


	// include the config file and editor class:
	include_once ($mosConfig_absolute_path.'/editor/wysiwygpro/config.php');
	include_once ($mosConfig_absolute_path.'/editor/wysiwygpro/editor_class.php');

	// create a new instance of the wysiwygPro class:
	$name = new wysiwygPro();

	$name->set_name($hiddenField);

	if ($hiddenField=='fulltext') {
		$name->subsequent(true);
	}

	$name->usep(true);

	// insert some HTML
	$name->set_code($content);

	// print the editor to the browser:
	$name->print_editor('100%', intval($height));

}

function getEditorContents( $editorArea, $hiddenField ) {
	global $mainframe;

	if(!$mainframe->set('loadEditor', true)) {
		return false;
	}
?>

submit_form();

<?php
}
?>
