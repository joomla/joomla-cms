<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Do not allow direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * TinyMCE Editor Plugin
 *
 * @package		Joomla
 * @subpackage	Editors
 * @since		1.5
 */
class plgEditorTinymce extends JPlugin
{
	/**
	 * Initialises the Editor.
	 *
	 * @return string JavaScript Initialization string
	 * @since 1.5
	 */
	function onInit()
	{
		$app		= JFactory::getApplication();
		$language	= JFactory::getLanguage();

		$mode	= (int) $this->params->get('mode',1);
		$theme	= array('simple','advanced','advanced');
		$skin	= $this->params->get('skin', '0');
		switch ($skin)
    	{
			case '3':
				$skin = "skin : \"o2k7\", skin_variant : \"black\",";
				break;

			case '2':
				$skin = "skin : \"o2k7\", skin_variant : \"silver\",";
				break;

			case '1':
				$skin = "skin : \"o2k7\",";
				break;
			case '0':
			default:
				$skin = "skin : \"default\",";
		}

		$compressed			= $this->params->def('compressed', 0);
		$cleanup_startup	= $this->params->def('cleanup_startup', 0);
		$cleanup_save		= $this->params->def('cleanup_save', 2);
		$entity_encoding	= $this->params->def('entity_encoding', 'raw');
		if ($cleanup_startup) {
			$cleanup_startup = 'true';
		}
		else {
			$cleanup_startup = 'false';
		}

		switch ($cleanup_save)
		{
			case '0':
				// Never clean up on save.
				$cleanup = 'false';
				break;

			case '1':
				// Clean up front end edits only.
				if ($app->isadmin()) {
					$cleanup = 'false';
				}
				else {
					$cleanup = 'true';
				}
				break;

			default:
				// Always clean up on save
				$cleanup = 'true';
		}

		$langMode			= $this->params->def('lang_mode', 0);
		$langPrefix			= $this->params->def('lang_code', 'en');
		if ($langMode) {
			$langPrefix = substr($language->getTag(), 0, strpos($language->getTag(), '-'));
		}
		if ($language->isRTL()) {
			$text_direction = 'rtl';
		}
		else {
			$text_direction = 'ltr';
		}

		$content_css		= $this->params->def('content_css', 1);
		$content_css_custom	= $this->params->def('content_css_custom', '');

		// loading of css file for `styles` dropdown
		if ($content_css_custom) {
			$content_css = 'content_css : "'. $content_css_custom .'", ';
		}
		else
		{
			// Lets get the default template for the site application
			$db = JFactory::getDBO();
			$db->setQuery(
				'SELECT template'
				. ' FROM #__template_styles'
				. ' WHERE client_id = 0'
				. ' AND home = 1'
			);
			$template = $db->loadResult();

			if ($content_css)
			{
				$file_path = JPATH_SITE .'/templates/'. $template .'/css/';
		        // Try for site css
		        $file_name = "template";
        		($language->isRTL()) ? $file_name .= "_rtl.css" :  $file_name .= ".css";

				if (file_exists($file_path .DS. $file_name)) {
				  $content_css = 'content_css : "' . JURI::root() .'templates/'. $template . '/css/'.$file_name.', ' . JURI::root() .'templates/system/css/editor.css"';
				}
				else {
				  $content_css = 'content_css : "' . JURI::root() .'templates/system/css/editor.css"';
       			}
			}
			else {
				$content_css = '';
			}
		}

		$relative_urls		= $this->params->def('relative_urls', '1');
		if ($relative_urls) { // relative
			$relative_urls = "true";
		} else { // absolute
			$relative_urls = "false";
		}

		$newlines			= $this->params->def('newlines', 0);
		if ($newlines) { // br
			$forcenewline = "force_br_newlines : \"true\", force_p_newlines : \"false\", forced_root_block : '',";
		} else { // p
			$forcenewline = "force_br_newlines : \"false\", force_p_newlines : \"true\", forced_root_block : 'p',";
		}
		$invalid_elements	= $this->params->def('invalid_elements', 'script,applet,iframe');
		$extended_elements	= $this->params->def('extended_elements', '');


		// theme_advanced_* settings
		$toolbar 			= $this->params->def('toolbar', 'top');
		$toolbar_align	= $this->params->def('toolbar_align', 'left');
		$html_height		= $this->params->def('html_height', '550');
		$html_width			= $this->params->def('html_width', '750');
		$element_path = '';
		if ($this->params->get('element_path', 1)) {
			$element_path = "theme_advanced_statusbar_location : \"bottom\", theme_advanced_path : true";
		} else {
			$element_path = "theme_advanced_statusbar_location : \"none\", theme_advanced_path : false";
		}

		$buttons1_add_before = $buttons1_add = array();
		$buttons2_add_before = $buttons2_add = array();
		$buttons3_add_before = $buttons3_add = array();
		$buttons4 = array();
		$plugins 	= array();
		if ($extended_elements != "") {
			$elements	= explode(',', $extended_elements);
		}

		//Initial values for buttons
	    array_push($buttons2_add_before,'cut','copy','paste');
	    array_push($buttons2_add,'|');

		// Plugins

		// fonts
		$fonts =  $this->params->def('fonts', 1);
		if ($fonts) {
			$buttons1_add[]	= 'fontselect,fontsizeselect';
		}

		// paste
		$paste =  $this->params->def('paste', 1);
		if ($paste)
		{
			$plugins[] = 'paste';
			$buttons2_add_before[] = 'pastetext,pasteword,selectall';
		}

		// search & replace
		$searchreplace = $this->params->def('searchreplace', 1);
		if ($searchreplace)
		{
			$plugins[]	= 'searchreplace';
			$buttons2_add_before[]	= '|,search,replace,|';
		}

		// insert date and/or time plugin
		$insertdate			= $this->params->def('insertdate', 1);
		$format_date		= $this->params->def('format_date', '%Y-%m-%d');
		$inserttime			= $this->params->def('inserttime', 1);
		$format_time		= $this->params->def('format_time', '%H:%M:%S');
		if ($insertdate or $inserttime)
		{
			$plugins[]	= 'insertdatetime';
			if ($insertdate) {
				$buttons2_add[]	= 'insertdate';
			}
			if ($inserttime) {
				$buttons2_add[]	= 'inserttime';
			}
		}

		// colors
		$colors =  $this->params->def('colors', 1);
		if ($colors) {
    		$buttons2_add[]	= 'forecolor,backcolor';
		}

		// table
		$table = $this->params->def('table', 1);
		if ($table)
		{
			$plugins[]	= 'table';
			$buttons3_add_before[]	= 'tablecontrols';
		}

		// emotions
		$smilies = $this->params->def('smilies', 1);
		if ($smilies)
		{
			$plugins[]	= 'emotions';
			$buttons3_add[]	= 'emotions';
		}

		//media plugin
		$media = $this->params->def('media', 1);
		if ($media)
		{
			$plugins[] = 'media';
			$buttons3_add[] = 'media';
		}

		// horizontal line
		$hr = $this->params->def('hr', 1);
		if ($hr)
		{
			$plugins[]	= 'advhr';
			$elements[] = 'hr[id|title|alt|class|width|size|noshade]';
			$buttons3_add[]	= 'advhr';
		}
		else {
			$elements[] = 'hr[id|class|title|alt]';
		}

		// rtl/ltr buttons
		$directionality	= $this->params->def('directionality', 1);
		if ($directionality)
		{
			$plugins[] = 'directionality';
			$buttons3_add[] = 'ltr,rtl';
		}

		// fullscreen
		$fullscreen	= $this->params->def('fullscreen', 1);
		if ($fullscreen)
		{
			$plugins[]	= 'fullscreen';
			$buttons3_add[]	= 'fullscreen';
		}

		// layer
		$layer = $this->params->def('layer', 1);
		if ($layer)
		{
			$plugins[]	= 'layer';
			$buttons4[]	= 'insertlayer';
			$buttons4[]	= 'moveforward';
			$buttons4[]	= 'movebackward';
			$buttons4[]	= 'absolute';
		}

		// style
		$style			= $this->params->def('style', 1);
		if ($style)
		{
			$plugins[]	= 'style';
			$buttons4[]	= 'styleprops';
		}

		// XHTMLxtras
		$xhtmlxtras			= $this->params->def('xhtmlxtras', 1);
		if ($xhtmlxtras)
		{
			$plugins[]	= 'xhtmlxtras';
			$buttons4[]	= 'cite,abbr,acronym,ins,del,attribs';
		}

		// visualchars
		$visualchars = $this->params->def('visualchars', 1);
		if ($template) {
			$plugins[]	= 'visualchars';
			$buttons4[]	= 'visualchars';
		}

		// visualchars
		$nonbreaking = $this->params->def('nonbreaking', 1);
		if ($template)
		{
			$plugins[]	= 'nonbreaking';
			$buttons4[]	= 'nonbreaking';
		}

		// template
		$template = $this->params->def('template', 1);
		if ($template)
		{
			$plugins[]	= 'template';
			$buttons4[]	= 'template';
		}

		// tinybrowser
		$tinybrowser	= $this->params->def('tinybrowser', 1);
		if ($tinybrowser) {
			$file_browser_callback	= "file_browser_callback : \"tinyBrowser\",";
		}
		else {
			$file_browser_callback	= "";
		}

		// advimage
		$advimage = $this->params->def('advimage', 1);
		if ($advimage)
		{
			$plugins[]	= 'advimage';
			$elements[]	= 'img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]';
		}

		// advlink
		$advlink 	= $this->params->def('advlink', 1);
		if ($advlink)
		{
			$plugins[]	= 'advlink';
			$elements[]	= 'a[class|name|href|target|title|onclick|rel]';
		}

		// autosave
		$autosave			= $this->params->def('autosave', 1);
		if ($autosave) {
			$plugins[]	= 'autosave';
		}

		// context menu
		$contextmenu	= $this->params->def('contextmenu', 1);
		if ($contextmenu) {
			$plugins[]	= 'contextmenu';
		}

		// inline popups
		$inlinepopups			= $this->params->def('inlinepopups', 1);
		if ($inlinepopups)
		{
			$plugins[]	= 'inlinepopups';
			$dialog_type = "dialog_type : \"modal\",";
		}
		else {
			$dialog_type = "";
		}

		// Safari compatibility
		$safari			= $this->params->def('safari', 0);
		if ($safari) {
			$plugins[]	= 'safari';
		}

		$custom_plugin = $this->params->def('custom_plugin', '');
		if ($custom_plugin != "") {
			$plugins[] = $custom_plugin;
		}

		$custom_button = $this->params->def('custom_button', '');
		if ($custom_button != "") {
			$buttons4[] = $custom_button;
		}

		// Prepare config variables
		$buttons1_add_before 	= implode(',', $buttons1_add_before);
		$buttons2_add_before 	= implode(',', $buttons2_add_before);
		$buttons3_add_before 	= implode(',', $buttons3_add_before);
		$buttons1_add 	= implode(',', $buttons1_add);
		$buttons2_add 	= implode(',', $buttons2_add);
		$buttons3_add 	= implode(',', $buttons3_add);
		$buttons4 	= implode(',', $buttons4);
		$plugins		= implode(',', $plugins);
		$elements 	= implode(',', $elements);

		switch($mode)
		{
			case 0:
				// Simple mode
				if ($compressed)
				{
					$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js\"></script>\n";
					$load .= "\t<script type=\"text/javascript\">
				    tinyMCE_GZ.init({
						themes : \"$theme[$mode]\",
						languages : \"". $langPrefix . "\"
						});
						</script>";
				}
				else {
					$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n";
				}
				$return = $load .
					"\t<script type=\"text/javascript\">
					tinyMCE.init({
					// General
					directionality: \"$text_direction\",
					editor_selector : \"mce_editable\",
					language : \"". $langPrefix . "\",
					mode : \"specific_textareas\",
					$skin
					theme : \"$theme[$mode]\",
					// Cleanup/Output
					cleanup : $cleanup,
					cleanup_on_startup : $cleanup_startup,
					entity_encoding : \"$entity_encoding\",
					$forcenewline
					// URL
					relative_urls : $relative_urls,
					remove_script_host : false,
					document_base_url : \"". JURI::root() ."\",
					// Layout
					$content_css
					});
					</script>";
		 		break;

			case 1: /* Advanced mode*/
				if ($compressed)
				{
					$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js\"></script>\n";
					$load .= "\t<script type=\"text/javascript\">
				    	tinyMCE_GZ.init({
							themes : \"$theme[$mode]\",
							languages : \"". $langPrefix . "\"
							});
							</script>";
				}
				else {
					$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n";
				}
				$return = $load .
					"\t<script type=\"text/javascript\">
					tinyMCE.init({
					// General
					directionality: \"$text_direction\",
					editor_selector : \"mce_editable\",
					language : \"". $langPrefix . "\",
					mode : \"specific_textareas\",
					$skin
					theme : \"$theme[$mode]\",
					// Cleanup/Output
					cleanup : $cleanup,
					cleanup_on_startup : $cleanup_startup,
					entity_encoding : \"$entity_encoding\",
					extended_valid_elements : \"$elements\",
					$forcenewline
					invalid_elements : \"$invalid_elements\",
					// URL
					relative_urls : $relative_urls,
					remove_script_host : false,
					document_base_url : \"". JURI::root() ."\",
					// Layout
					$content_css,
					// Advanced theme
					theme_advanced_toolbar_location : \"$toolbar\",
					theme_advanced_toolbar_align : \"$toolbar_align\",
					theme_advanced_source_editor_height : \"$html_height\",
					theme_advanced_source_editor_width : \"$html_width\",
					$element_path
					});
					</script>";
			  	break;

			case 2:
				// Extended mode
				if ($compressed)
				{
					$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js\"></script>\n";
					$load .= "\t<script type=\"text/javascript\">
						tinyMCE_GZ.init({
						themes : \"$theme[$mode]\",
						plugins : \"$plugins\",
						languages : \"". $langPrefix . "\"
						});
						</script>";
				}
				else {
					$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n";
				}
				$return = $load .
					"\t<script type=\"text/javascript\">
					tinyMCE.init({
					// General
					$dialog_type
					directionality: \"$text_direction\",
					editor_selector : \"mce_editable\",
					language : \"". $langPrefix . "\",
					mode : \"specific_textareas\",
					plugins : \"$plugins\",
					$skin
					theme : \"$theme[$mode]\",
					// Callbacks
					$file_browser_callback
					// Cleanup/Output
					cleanup : $cleanup,
					cleanup_on_startup : $cleanup_startup,
					entity_encoding : \"$entity_encoding\",
					extended_valid_elements : \"$elements\",
					$forcenewline
					invalid_elements : \"$invalid_elements\",
					// URL
					relative_urls : $relative_urls,
					remove_script_host : false,
					document_base_url : \"". JURI::root() ."\",
					// Layout
					$content_css,
					// Advanced theme
					theme_advanced_toolbar_location : \"$toolbar\",
					theme_advanced_toolbar_align : \"$toolbar_align\",
					theme_advanced_source_editor_height : \"$html_height\",
					theme_advanced_source_editor_width : \"$html_width\",
					$element_path,
					theme_advanced_buttons1_add_before : \"$buttons1_add_before\",
					theme_advanced_buttons2_add_before : \"$buttons2_add_before\",
					theme_advanced_buttons3_add_before : \"$buttons3_add_before\",
					theme_advanced_buttons1_add : \"$buttons1_add\",
					theme_advanced_buttons2_add : \"$buttons2_add\",
					theme_advanced_buttons3_add : \"$buttons3_add\",
					theme_advanced_buttons4 : \"$buttons4\",
					plugin_insertdate_dateFormat : \"$format_date\",
					plugin_insertdate_timeFormat : \"$format_time\",
					fullscreen_settings : {
					theme_advanced_path_location : \"top\"
					}
					});
					</script>";
					if ($tinybrowser) {
						$return .="\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/plugins/tinybrowser/tb_tinymce.js.php\"></script>\n";
						setcookie('tinybrowser_lang', $langPrefix, 0, "/");
					}
				break;
		}

		return $return;
	}

	/**
	 * TinyMCE WYSIWYG Editor - get the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onGetContent($editor)
	{
		return "tinyMCE.activeEditor.getContent();";
	}

	/**
	 * TinyMCE WYSIWYG Editor - set the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onSetContent($editor, $html)
	{
		return "tinyMCE.activeEditor.setContent(".$html.");";
	}

	/**
	 * TinyMCE WYSIWYG Editor - copy editor content to form field
	 *
	 * @param string 	The name of the editor
	 */
	function onSave($editor)
	{
		return "tinyMCE.triggerSave();";
	}

	function onGetInsertMethod($name)
	{
		static $done = false;

		// Do this only once.
		if (!$done)
		{
			$done = true;
			$doc = JFactory::getDocument();
			$js = "function jInsertEditorText(text, editor) {
				tinyMCE.execInstanceCommand(editor, 'mceInsertContent',false,text);
			}";
			$doc->addScriptDeclaration($js);
		}

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param	string	The name of the editor area.
	 * @param	string	The content of the field.
	 * @param	string	The width of the editor area.
	 * @param	string	The height of the editor area.
	 * @param	int		The number of columns for the editor area.
	 * @param	int		The number of rows for the editor area.
	 * @param	boolean	True and the editor buttons will be displayed.
	 * @param	string	An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 */
	function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null)
	{
		if (empty($id)) {
			$id = $name;
		}

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width)) {
			$width .= 'px';
		}
		if (is_numeric($height)) {
			$height .= 'px';
		}

		$editor  = "<textarea id=\"$name\" name=\"$id\" cols=\"$col\" rows=\"$row\" style=\"width:{$width}; height:{$height};\" class=\"mce_editable\">$content</textarea>\n" .
		$this->_displayButtons($name, $buttons) .
		$this->_toogleButton($name);

		return $editor;
	}

	function _displayButtons($name, $buttons)
	{
		// Load modal popup behavior
		JHTML::_('behavior.modal', 'a.modal-button');

		$args['name'] = $name;
		$args['event'] = 'onGetInsertMethod';

		$return = '';
		$results[] = $this->update($args);
		foreach ($results as $result) {
			if (is_string($result) && trim($result)) {
				$return .= $result;
			}
		}

		if(!empty($buttons))
		{
			$results = $this->_subject->getButtons($name, $buttons);

			/*
			 * This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			 */
			$return .= "\n<div id=\"editor-xtd-buttons\">\n";
			foreach ($results as $button)
			{
				/*
				 * Results should be an object
				 */
				if ($button->get('name'))
				{
					$modal		= ($button->get('modal')) ? 'class="modal-button"' : null;
					$href		= ($button->get('link')) ? 'href="'.JURI::base().$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$return .= "<div class=\"button2-left\"><div class=\"".$button->get('name')."\"><a ".$modal." title=\"".$button->get('text')."\" ".$href." ".$onclick." rel=\"".$button->get('options')."\">".$button->get('text')."</a></div></div>\n";
				}
			}
			$return .= "</div>\n";
		}

		return $return;
	}

	function _toogleButton($name)
	{
		$return  = '';
		$return .= "\n<div style=\"margin-top:-5px\">\n";
		$return .= "<div class=\"button2-left\"><div class=\"blank\"><a href=\"#\" onclick=\"javascript:tinyMCE.execCommand('mceToggleEditor', false, '$name');return false;\" title=\"Toggle editor\">Toggle editor</a></div></div>";
		$return .= "</div>\n";
		return $return;
	}
}