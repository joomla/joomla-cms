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

jimport('joomla.event.plugin');

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
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function plgEditorXstandard(& $subject, $params) {
		parent::__construct($subject, $params);
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
		global $mainframe;

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$html = '';
		ob_start();
		?>
  		<script type="text/javascript" src="<?php echo $url; ?>plugins/editors/xstandard/xstandard.js"></script>
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
		return;
	}

	/**
	 * XStandard Lite WYSIWYG Editor - set the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onSetContent( $editor, $html ) {
		return ;
	}

	/**
	 * XStandard Lite WYSIWYG Editor - copy editor content to form field
	 *
	 * @param string 	The name of the editor
	 */
	function onSave( $editor ) {

		$js = "var editor = document.getElementById('xstandard');\n";
		$js .= "editor.EscapeUnicode = true;";
		$js .= "$('text').value = editor.value;";

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
	 */
	function onDisplay( $name, $content, $width, $height, $col, $row )
	{
		global $mainframe;

		jimport('joomla.environment.browser');
		$instance	=& JBrowser::getInstance();
		$language	=& JFactory::getLanguage();
		$url		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		if ($language->isRTL()) {
			$text_direction = 'rtl';
		} else {
			$text_direction = 'ltr';
		}
		$html = '';
		ob_start();
		?>

		<div style="border: 1px solid #D5D5D5">
		<object type="application/x-xstandard" id="xstandard" class="<?php echo $name ?>" width="<?php echo $width ?>" height="<?php echo $height ?>">
 			<param name="Value" value="<?php echo convertToXML($content) ?>" />

 			<param name="Lang" value="en" />
 			<param name="Dir" value="<?php echo $text_direction ?>" />
			<param name="EnablePasteMarkup" value="yes" />
			<param name="EnableTimestamp" value="no" />
			<param name="EscapeUnicode" value="no" />
			<param name="Options" value="32768" />
			<param name="Toolbar" value="numbering, bullets, , draw-layout-table, draw-data-table, image, line, hyperlink, attachment, directory, undo, , wysiwyg, source, preview, screen-reader, ,expand" />

 			<param name="BorderColor" value="#FFF" />
 			<param name="Base" value="<?php echo $url ?>" />
 			<param name="ExpandWidth" value="100%" />
 			<param name="ExpandHeight" value="400" />

 			<param name="LatestVersion" value="1.7.1.0" />

 			<param name="Namespaces" value="xmlns:joomla='http://joomla.org'" />
			<param name="CustomBlockElements" value="joomla:pagebreak" />
			<param name="CustomInlineElements" value="joomla:readmore,joomla:image" />

			<param name="CustomEmptyElements" value="joomla:readmore,joomla:image" />

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

		return $html;
	}

	function _getTemplateCss()
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$url		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

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

		$content_css = $url .'templates/'. $template .'/css/';

		$file_path = JPATH_SITE .'/templates/'. $template .'/css/';
		if ( file_exists( $file_path .DS. 'editor.css' ) ) {
			$content_css = $content_css . 'editor.css' .'", ';
		} else {
			$content_css = $content_css . 'template_css.css", ';
		}

		return $content_css;
	}

}

function convertToXML($content)
{
	$content = preg_replace( '/{image\s*.*?}/i', '<joomla:image />', $content );
	$content = preg_replace( '/{pagebreak\s*.*?}/i', '<joomla:pagebreak />', $content );
	$content = preg_replace( '/{readmore\s*.*?}/i', '<joomla:readmore />', $content );
	return $content;
}
?>