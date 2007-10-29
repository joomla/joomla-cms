<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Do not allow direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * XStandard Lite for Joomla! WYSIWYG Editor Plugin
 *
 * @author Johan Janssens <johan.janssens@joomla.org>
 * @package Editors
 * @since 1.5
 */
class plgEditorXstandard extends JPlugin {

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgEditorXstandard(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	/**
	 * Method to handle the onInitEditor event.
	 *  - Initializes the XStandard Lite WYSIWYG Editor
	 *
	 * @access public
	 * @return string JavaScript Initialization string
	 * @since 1.5
	 */
	function onInit()
	{
		$html = '';
		ob_start();
		?>
  		<script type="text/javascript" src="<?php echo JURI::root() ?>/plugins/editors/xstandard/xstandard.js"></script>
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * XStandard Lite WYSIWYG Editor - get the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onGetContent( $editor ) {
		return "$('xstandard').value;";
	}

	/**
	 * XStandard Lite WYSIWYG Editor - set the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onSetContent( $editor, $html ) {
		return "$('xstandard').value =". $html .";";
	}

	/**
	 * XStandard Lite WYSIWYG Editor - copy editor content to form field
	 *
	 * @param string 	The name of the editor
	 */
	function onSave( $editor ) {

		$js = "var editor = $('xstandard');\n";
		$js .= "editor.EscapeUnicode = true;";
		$js .= "$('".$editor."').value = editor.value;";

		return $js;
	}

	/**
	 * XStandard Lite WYSIWYG Editor - display the editor
	 *
	 * @param string The name of the editor area
	 * @param string The content of the field
	 * @param string The name of the form field
	 * @param string The width of the editor area
	 * @param string The height of the editor area
	 * @param int The number of columns for the editor area
	 * @param int The number of rows for the editor area
	 * @param mixed Can be boolean or array.
	 */
	function onDisplay( $name, $content, $width, $height, $col, $row, $buttons = true )
	{
		// Load modal popup behavior
		JHTML::_('behavior.modal', 'a.modal-button');
		
		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric( $width )) {
			$width .= 'px';
		}
		if (is_numeric( $height )) {
			$height .= 'px';
		}

		jimport('joomla.environment.browser');
		$instance	=& JBrowser::getInstance();
		$language	=& JFactory::getLanguage();
		$db			=& JFactory::getDBO();
		
		$lang = substr( $language->getTag(), 0, strpos( $language->getTag(), '-' ) );
		
		if ($language->isRTL()) {
			$direction = 'rtl';
		} else {
			$direction = 'ltr';
		}
		
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

		$file_path = JPATH_SITE .'/templates/'. $template .'/css/';
		if ( !file_exists( $file_path .DS. 'editor.css' ) ) {
			$template = 'system';
		} 
				
		$css =  JURI::root() .'/templates/'. $template . '/css/editor.css';
		
		$html = '';
		ob_start();
		?>

		<div style="border: 1px solid #D5D5D5">
		<object type="application/x-xstandard" id="xstandard" class="<?php echo $name ?>" width="<?php echo $width ?>" height="<?php echo $height ?>">
 			<param name="Value" value="<?php echo $content ?>" />

 			<param name="Lang" value="<?php echo $lang ?>" />
 			<param name="Dir" value="<?php echo $direction ?>" />
 			<param name="EditorCSS" value="<?php echo $css ?>" />
			<param name="EnablePasteMarkup" value="yes" />
			<param name="EnableTimestamp" value="no" />
			<param name="EscapeUnicode" value="no" />
			<param name="ToolbarWysiwyg" value="line, hyperlink, attachment, directory, undo, , wysiwyg, source, screen-reader, ,expand; strong, em, underline, strikethrough, , align-left, align-center, align-right, , blockquote, undo-blockquote, ,numbering, bullets, , undo, redo, ,layout-table, data-table, draw-layout-table, draw-data-table" />
			<param name="ToolbarSource" value="indent, whitespace, word-wrap, dim-tags, validate,, wysiwyg, source, screen-reader, , expand" />
			<param name="ToolbarPreview" value="wysiwyg, source, screen-reader, ,expand" />
			<param name="ToolbarScreenReader" value="wysiwyg, source, screen-reader, , expand" />
			<param name="BackgroundColor" value="#F9F9F9" />
			<param name="Mode" value="<?php echo $this->params->get('mode', 'wysiwyg'); ?>" />
			<param name="IndentOutput" value="yes" />

 			<param name="BorderColor" value="#FFF" />
 			<param name="Base" value="<?php echo $url ?>" />
 			<param name="ExpandWidth" value="800" />
 			<param name="ExpandHeight" value="600" />

 			<param name="LatestVersion" value="2.0.0.0" />

 			<param name="CMSCode" value="065126D6-357D-46FC-AF74-A1F5B2D5036E" />
 			<param name="CMSImageLibraryURL" value="<?php echo $url ?>plugins/editors/xstandard/imagelibrary.php" />
			<param name="CMSAttachmentLibraryURL" value="<?php echo $url ?>plugins/editors/xstandard/attachmentlibrary.php" />
			<param name="CMSDirectoryURL" value="<?php echo $url ?>plugins/editors/xstandard/directory.php" />
			<param name="PreviewXSLT" value="<?php echo $url ?>plugins/editors/xstandard/preview.xsl" />

			<param name="CSS" value="<?php echo $this->_getTemplateCss(); ?>" />

			<textarea name="alternate1" id="alternate1" cols="60" rows="15"><?php echo $content ?></textarea>
		</object>
 		<input type="hidden" id="<?php echo $name ?>" name="<?php echo $name ?>" value="" />
 		</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		
		$html .= $this->_displayButtons($name, $buttons);

		return $html;
	}
	
	function onGetInsertMethod($name)
	{
		$doc = & JFactory::getDocument();

		$js= "function jInsertEditorText( text ) {
			var editor = document.getElementById('xstandard');
			editor.InsertXML(text);
		}";
		$doc->addScriptDeclaration($js);

		return true;
	}

	function _getTemplateCss()
	{
		$db			=& JFactory::getDBO();

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

		$content_css = JURI::root() .'/templates/'. $template .'/css/';

		$file_path = JPATH_SITE .'/templates/'. $template .'/css/';
		if ( file_exists( $file_path .DS. 'editor.css' ) ) {
			$content_css = $content_css . 'editor.css' .'", ';
		} else {
			$content_css = $content_css . 'template_css.css", ';
		}

		return $content_css;
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
					$href		= ($button->get('link')) ? 'href="'.$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$return .= "<div class=\"button2-left\"><div class=\"".$button->get('name')."\"><a ".$modal." title=\"".$button->get('text')."\" ".$href." ".$onclick." rel=\"".$button->get('options')."\">".$button->get('text')."</a></div></div>\n";
				}
			}
			$return .= "</div>\n";
		}
		
		return $return;
	}

}
?>