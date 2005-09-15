<?php
/**
* @version $Id: tinymce.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onInitEditor', 'botTinymceEditorInit' );
$_MAMBOTS->registerFunction( 'onGetEditorContents', 'botTinymceEditorGetContents' );
$_MAMBOTS->registerFunction( 'onEditorArea', 'botTinymceEditorEditorArea' );

/**
* TinyMCE WYSIWYG Editor - javascript initialisation
*/
function botTinymceEditorInit() {
	global $mosConfig_live_site, $database, $mosConfig_absolute_path;

	// load tinymce info
	$query = "SELECT id FROM #__mambots WHERE element = 'tinymce' AND folder = 'editors'";
	$database->setQuery( $query );
	$id = $database->loadResult();
	$mambot = new mosMambot( $database );
	$mambot->load( $id );
	$params = new mosParameters( $mambot->params );

	$theme = $params->get( 'theme', 'default' );

 	$toolbar 			= $params->def( 'toolbar', 'top' );
 	$html_height		= $params->def( 'html_height', '550' );
 	$html_width			= $params->def( 'html_width', '750' );
 	$text_direction		= $params->def( 'text_direction', 'ltr' );
	$content_css		= $params->def( 'content_css', 1 );
 	$content_css_custom	= $params->def( 'content_css_custom', '' );
 	$invalid_elements	= $params->def( 'invalid_elements', 'script,applet,iframe' );
 	$newlines			= $params->def( 'newlines', 'false' );

	// Plugins
	// preview
	$preview			= $params->def( 'preview', 1 );
 	$preview_height		= $params->def( 'preview_height', '550' );
 	$preview_width		= $params->def( 'preview_width', '750' );
	// insert date
	$insertdate			= $params->def( 'insertdate', 1 );
 	$format_date		= $params->def( 'format_date', '%Y-%m-%d' );
	// insert time
	$inserttime			= $params->def( 'inserttime', 1 );
 	$format_time		= $params->def( 'format_time', '%H:%M:%S' );
	// search & replace
	$searchreplace		=  $params->def( 'searchreplace', 1 );
	// emotions
	$smilies			=  $params->def( 'smilies', 1 );
	// flash
	$flash				=  $params->def( 'flash', 1 );
	// table
	$table				=  $params->def( 'table', 1 );
	// horizontal line
	$hr					=  $params->def( 'hr', 1 );
	// fullscreen
	$fullscreen			=  $params->def( 'fullscreen', 1 );
	// contextmenu
	$contextmenu		=  $params->def( 'contextmenu', 1 );

 	if ( $content_css ) {
 		$query = "SELECT template FROM #__templates_menu WHERE client_id='0' AND menuid='0'";
 		$database->setQuery( $query );
 		$template 		= $database->loadResult();
 		$content_css	= 'content_css : "'. $mosConfig_live_site .'/templates/'. $template .'/css/template_css.css"';
 	} else {
 		if ( $content_css_custom ) {
 			$content_css = 'content_css : "'. $content_css_custom .'"';
 		} else {
 			$content_css = '';
 		}
 	}

	$plugins[] 	= '';
	$buttons2[]	= '';
	$buttons3[]	= '';
	$elements[]	= '';

	// preview
	if ( $preview ) {
		$plugins[]	= 'preview';
		$buttons2[]	= 'preview';
	}
	// search & replace
	if ( $searchreplace ) {
		$plugins[]	= 'searchreplace';
		$buttons2[]	= 'search,replace';
	}
	$plugins[]	= 'insertdatetime';
	// insert date
	if ( $insertdate ) {
		$buttons2[]	= 'insertdate';
	}
	// insert time
	if ( $inserttime ) {
		$buttons2[]	= 'inserttime';
	}
	// emotions
	if ( $smilies ) {
		$plugins[]	= 'emotions';
		$buttons2[]	= 'emotions';
	}

	// horizontal line
	if ( $hr ) {
		$plugins[]	= 'advhr';
		$elements[] = 'hr[class|width|size|noshade]';
		$buttons3[]	= 'advhr';
	}
	// flash
	if ( $flash ) {
		$plugins[]	= 'flash';
		$buttons3[]	= 'flash';
	}
	// table
	if ( $table ) {
		$plugins[]	= 'table';
		$buttons3[]	= 'tablecontrols';
	}
	// fullscreen
	if ( $fullscreen ) {
    	$plugins[]	= 'fullscreen';
    	$buttons3[]	= 'fullscreen';
	}
	// contextmenu
	if ( $contextmenu ) {
    	$plugins[]  = "contextmenu";
	}

	$buttons2 	= implode( ',', $buttons2 );
	$buttons3 	= implode( ',', $buttons3 );
	$plugins 	= implode( ',', $plugins );
	$elements 	= implode( ',', $elements );

	return <<<EOD
<script type="text/javascript" src="$mosConfig_live_site/mambots/editors/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		theme : "$theme",
		language : "en",
		mode : "specific_textareas",
		document_base_url : "$mosConfig_live_site/",
		relative_urls : false,
        remove_script_host : false,
        save_callback : "MamboSave",
 		invalid_elements : "$invalid_elements",
 		theme_advanced_toolbar_location : "$toolbar",
 		theme_advanced_source_editor_height : "$html_height",
		theme_advanced_source_editor_width : "$html_width",
 		directionality: "$text_direction",
 		force_br_newlines : "$newlines",
 		$content_css,
		debug : false,
		plugins : "advlink, advimage, $plugins",
		theme_advanced_buttons2_add : "$buttons2",
		theme_advanced_buttons3_add : "$buttons3",
	    plugin_insertdate_dateFormat : "$format_date",
	    plugin_insertdate_timeFormat : "$format_time",
	    plugin_preview_width : "$preview_width",
	    plugin_preview_height : "$preview_height",
	    extended_valid_elements : "a[name|href|target|title|onclick], img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name], $elements",
    	fullscreen_settings : {
    	       theme_advanced_path_location : "top"
    	}
	});
	function MamboSave(editor_id, content, node)
   	{
   		base_url = tinyMCE.settings['document_base_url'];
     	var vHTML = node.innerHTML;
      	if (true == true){
         	vHTML = tinyMCE.regexpReplace(vHTML, 'href\s*=\s*"?'+base_url+'', 'href="', 'gi');
           	vHTML = tinyMCE.regexpReplace(vHTML, 'src\s*=\s*"?'+base_url+'', 'src="', 'gi');
    	}
       	return vHTML;
  	}
</script>

EOD;
}
/**
* TinyMCE WYSIWYG Editor - copy editor contents to form field
* @param string The name of the editor area
* @param string The name of the form field
*/
function botTinymceEditorGetContents( $editorArea, $hiddenField ) {
	return <<<EOD

		tinyMCE.triggerSave();
EOD;
}
/**
* TinyMCE WYSIWYG Editor - display the editor
* @param string The name of the editor area
* @param string The content of the field
* @param string The name of the form field
* @param string The width of the editor area
* @param string The height of the editor area
* @param int The number of columns for the editor area
* @param int The number of rows for the editor area
* @param int Whether to show the Editor buttons or not - by default set to yes
*/
function botTinymceEditorEditorArea( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut=1 ) {
	global $mosConfig_live_site, $_MAMBOTS;

	$buttons = '';
	// show buttons
	if ( $showbut ) {
		$results = $_MAMBOTS->trigger( 'onCustomEditorButton' );
		$buttons = array();
		foreach ($results as $result) {
		    $buttons[] = '<img src="'.$mosConfig_live_site.'/mambots/editors-xtd/'.$result[0].'" onclick="tinyMCE.execCommand(\'mceInsertContent\',false,\''.$result[1].'\')" />';
		}
		$buttons = implode( '', $buttons );
	}

	return <<<EOD
<textarea id="$hiddenField" name="$hiddenField" cols="$col" rows="$row" style="width:{$width}px; height:{$height}px;" mce_editable="true">$content</textarea>
<br />$buttons
EOD;
}
?>
