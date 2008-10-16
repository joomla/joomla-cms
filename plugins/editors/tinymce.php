<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Do not allow direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * TinyMCE WYSIWYG Editor Plugin
 *
 * @package Editors
 * @since 1.5
 */
class plgEditorTinymce extends JPlugin
{
	/**
	 * Method to handle the onInit event.
	 *  - Initializes the TinyMCE WYSIWYG Editor
	 *
	 * @access public
	 * @return string JavaScript Initialization string
	 * @since 1.5
	 */
	function onInit()
	{
		global $mainframe;
		$db			=& JFactory::getDBO();
		$language	=& JFactory::getLanguage();

		$theme = $this->params->get( 'theme', 'advanced' );
		// handling for former default option
		if ($theme == 'default' ) {
			$theme = 'advanced';
		}

		$toolbar 			= $this->params->def( 'toolbar', 'top' );
		$html_height		= $this->params->def( 'html_height', '550' );
		$html_width			= $this->params->def( 'html_width', '750' );
		$content_css		= $this->params->def( 'content_css', 1 );
		$content_css_custom	= $this->params->def( 'content_css_custom', '' );
		$invalid_elements	= $this->params->def( 'invalid_elements', 'script,applet,iframe' );
		$newlines			= $this->params->def( 'newlines', 0 );
		$cleanup_startup	= $this->params->def( 'cleanup_startup', 0 );
		$cleanup_save		= $this->params->def( 'cleanup_save', 2 );
		$compressed			= $this->params->def( 'compressed', 0 );
		$langPrefix			= $this->params->def( 'lang_code', 'en' );
		$langMode			= $this->params->def( 'lang_mode', 0 );
		$relative_urls		= $this->params->def( 'relative_urls', 		0 );
		$clear_entities		= $this->params->def( 'clear_entities', 0 );
		$extended_elements	= $this->params->def( 'extended_elements', '' );

		$plugins 	= array();
		$buttons2	= array();
		$buttons3	= array();
		$elements	= explode( ',', $extended_elements );

		// search & replace
		$searchreplace 		=  $this->params->def( 'searchreplace', 1 );
		if ( $searchreplace ) {
			$plugins[]	= 'searchreplace';
			$buttons2[]	= 'search,replace';
		}

		$plugins[]	= 'insertdatetime';

		// insert date
		$insertdate			= $this->params->def( 'insertdate', 1 );
		$format_date		= $this->params->def( 'format_date', '%Y-%m-%d' );
		if ( $insertdate ) {
			$buttons2[]	= 'insertdate';
		}

		// insert time
		$inserttime			= $this->params->def( 'inserttime', 1 );
		$format_time		= $this->params->def( 'format_time', '%H:%M:%S' );
		if ( $inserttime ) {
			$buttons2[]	= 'inserttime';
		}

		// emotions
		$smilies 			=  $this->params->def( 'smilies', 0 );
		if ( $smilies ) {
			$plugins[]	= 'emotions';
			$buttons2[]	= 'emotions';
		}

		//media plugin
		$plugins[] = 'media';
		$buttons2[] = 'media';

		// horizontal line
		$hr 				=  $this->params->def( 'hr', 1 );
		if ( $hr ) {
			$plugins[]	= 'advhr';
			$elements[] = 'hr[id|title|alt|class|width|size|noshade]';
			$buttons3[]	= 'advhr';
		} else {
			$elements[] = 'hr[id|class|title|alt]';
		}

		// table
		$table			=  $this->params->def( 'table', 1 );
		if ( $table ) {
			$plugins[]	= 'table';
			$buttons3[]	= 'tablecontrols';
		}
		// fullscreen
		$fullscreen			=  $this->params->def( 'fullscreen', 1 );
		if ( $fullscreen ) {
			$plugins[]	= 'fullscreen';
			$buttons3[]	= 'fullscreen';
		}

		// rtl/ltr buttons
		$directionality		=  $this->params->def( 'directionality', 1 );
		if ( $directionality ) {
			$plugins[] = 'directionality';
			$buttons2[] = 'ltr,rtl';
		}

		// autosave
		$autosave			= $this->params->def( 'autosave', 0 );
		if ( $autosave ) {
			$plugins[]	= 'autosave';
		}

		// layer
		$layer			= $this->params->def( 'layer', 1 );
		if ( $layer ) {
			$plugins[]	= 'layer';
			$buttons2[]	= 'insertlayer';
			$buttons2[]	= 'moveforward';
			$buttons2[]	= 'movebackward';
			$buttons2[]	= 'absolute';
		}

		// style
		$style			= $this->params->def( 'style', 1 );
		if ( $style ) {
			$plugins[]	= 'style';
			$buttons3[]	= 'styleprops';
		}

		// XHTMLxtras
		$xhtmlxtras			= $this->params->def( 'xhtmlxtras', 0 );
		if ( $xhtmlxtras ) {
			$plugins[]	= 'xhtmlxtras';
			$buttons3[]	= 'cite';
			$buttons3[]	= 'abbr';
			$buttons3[]	= 'acronym';
			$buttons3[]	= 'ins';
			$buttons3[]	= 'del';
			$buttons3[]	= 'attribs';
		}

		// template
		$template			= $this->params->def( 'template', 0 );
		if ( $template ) {
			$plugins[]	= 'template';
			$buttons3[]	= 'template';
		}

		// text color
		$buttons2[] = 'forecolor';

		if ($language->isRTL()) {
			$text_direction = 'rtl';
		} else {
			$text_direction = 'ltr';
		}

		$entities = '';
		if ($clear_entities) {
			$entities = 'entities : "160,nbsp,38,amp,34,quot,162,cent,8364,euro,163,pound,165,yen,169,copy,174,reg,8482,trade,8240,permil,60,lt,62,gt,8804,le,8805,ge,176,deg,8722,minus",';
		}

		$element_path = '';
		if($this->params->get('element_path', 0)) {
			$element_path = "theme_advanced_statusbar_location : \"bottom\", theme_advanced_path : true,";
		}

		if ( $langMode ) {
			$langPrefix = substr( $language->getTag(), 0, strpos( $language->getTag(), '-' ) );
		}
		// loading of css file for `styles` dropdown
		if ( $content_css_custom ) {
			$content_css = 'content_css : "'. $content_css_custom .'", ';
		}
		else
		{
			/*
			 * Lets get the default template for the site application
			 */
			$query = 'SELECT template'
			. ' FROM #__templates_menu'
			. ' WHERE client_id = 0'
			. ' AND menuid = 0'
			;
			$db->setQuery( $query );
			$template = $db->loadResult();

			if($content_css)
			{
				$file_path = JPATH_SITE .'/templates/'. $template .'/css/';
				if ( !file_exists( $file_path .DS. 'editor.css' ) ) {
					$template = 'system';
				}

				$content_css = 'content_css : "' . JURI::root() .'templates/'. $template . '/css/editor.css",';
			} else {
				$content_css = '';
			}
		}

		if ( $cleanup_startup ) {
			$cleanup_startup = 'true';
		} else {
			$cleanup_startup = 'false';
		}

		switch ( $cleanup_save ) {
		case '0': /* Never clean up on save */
			$cleanup = 'false';
			break;
		case '1': /* Clean up front end edits only */
			if ($mainframe->isadmin())
				$cleanup = 'false';
			else
				$cleanup = 'true';
			break;
		default:  /* Always clean up on save */
			$cleanup = 'true';
		}

		if ( $newlines ) {
			$br_newlines	= 'true';
			$p_newlines		= 'false';
		} else {
			$br_newlines	= 'false';
			$p_newlines		= 'true';
		}

		// Tiny Compressed mode
		if ( $compressed ) {
			$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce_gzip.php\"></script>\n";
		} else {
			$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n";
		}

		$buttons2 	= implode( ',', $buttons2 );
		$buttons3 	= implode( ',', $buttons3 );
		$plugins 	= implode( ',', $plugins );
		$elements 	= implode( ',', $elements );

		$return = $load .
			"\t<script type=\"text/javascript\">
			tinyMCE.init({
			theme : \"$theme\",
			language : \"". $langPrefix . "\",
			mode : \"textareas\",
			gecko_spellcheck : \"true\",
			editor_selector : \"mce_editable\",
			document_base_url : \"". JURI::root() ."\",
			entities : \"60,lt,62,gt\",
			relative_urls : $relative_urls,
			remove_script_host : false,
			save_callback : \"TinyMCE_Save\",
			invalid_elements : \"$invalid_elements\",
			extended_valid_elements : \"a[class|name|href|target|title|onclick|rel],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],$elements\",
			theme_advanced_toolbar_location : \"$toolbar\",
			theme_advanced_source_editor_height : \"$html_height\",
			theme_advanced_source_editor_width : \"$html_width\",
			directionality: \"$text_direction\",
			force_br_newlines : \"$br_newlines\",
			force_p_newlines : \"$p_newlines\",
			$content_css
			debug : false,
			cleanup : $cleanup,
			cleanup_on_startup : $cleanup_startup,
			safari_warning : false,
			plugins : \"advlink, advimage, $plugins\",
			theme_advanced_buttons1_add : \"fontselect\",
			theme_advanced_buttons2_add : \"$buttons2\",
			theme_advanced_buttons3_add : \"$buttons3\",
			theme_advanced_disable : \"help\",
			plugin_insertdate_dateFormat : \"$format_date\",
			plugin_insertdate_timeFormat : \"$format_time\",
			$entities
			$element_path
			fullscreen_settings : {
				theme_advanced_path_location : \"top\"
			}
		});
		function TinyMCE_Save(editor_id, content, node)
		{
			base_url = tinyMCE.settings['document_base_url'];
			var vHTML = content;
			if (true == true){
				vHTML = tinyMCE.regexpReplace(vHTML, 'href\s*=\s*\"?'+base_url+'', 'href=\"', 'gi');
				vHTML = tinyMCE.regexpReplace(vHTML, 'src\s*=\s*\"?'+base_url+'', 'src=\"', 'gi');
				vHTML = tinyMCE.regexpReplace(vHTML, 'mce_real_src\s*=\s*\"?', '', 'gi');
				vHTML = tinyMCE.regexpReplace(vHTML, 'mce_real_href\s*=\s*\"?', '', 'gi');
			}
			return vHTML;
		}
	</script>";

		return $return;
	}

	/**
	 * TinyMCE WYSIWYG Editor - get the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onGetContent( $editor ) {
		return "tinyMCE.getContent();";
	}

	/**
	 * TinyMCE WYSIWYG Editor - set the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onSetContent( $editor, $html ) {
		return "tinyMCE.setContent(".$html.");";
	}

	/**
	 * TinyMCE WYSIWYG Editor - copy editor content to form field
	 *
	 * @param string 	The name of the editor
	 */
	function onSave( $editor ) {
		return "tinyMCE.triggerSave();";
	}

	/**
	 * TinyMCE WYSIWYG Editor - display the editor
	 *
	 * @param string The name of the editor area
	 * @param string The content of the field
	 * @param string The width of the editor area
	 * @param string The height of the editor area
	 * @param int The number of columns for the editor area
	 * @param int The number of rows for the editor area
	 * @param mixed Can be boolean or array.
	 */
	function onDisplay( $name, $content, $width, $height, $col, $row, $buttons = true)
	{
		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric( $width )) {
			$width .= 'px';
		}
		if (is_numeric( $height )) {
			$height .= 'px';
		}

		$buttons = $this->_displayButtons($name, $buttons);
		$editor  = "<textarea id=\"$name\" name=\"$name\" cols=\"$col\" rows=\"$row\" style=\"width:{$width}; height:{$height};\" class=\"mce_editable=\">$content</textarea>\n" . $buttons;

		return $editor;
	}

	function onGetInsertMethod($name)
	{
		$doc = & JFactory::getDocument();

		$js= "function jInsertEditorText( text, editor ) {
			tinyMCE.execInstanceCommand(editor, 'mceInsertContent',false,text);
		}";
		$doc->addScriptDeclaration($js);

		return true;
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
				if ( $button->get('name') )
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
}