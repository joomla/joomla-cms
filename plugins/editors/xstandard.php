<?php
/**
 * @version $Id: tinymce.php 1820 2006-01-14 20:29:16Z stingrey $
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Do not allow direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.extension.plugin' );

/**
 * XStandard Lite for Joomla! WYSIWYG Editor Plugin
 *
 * @author Johan Janssens <johan@joomla.be>
 * @package Editors
 * @since 1.1
 */
class JEditor_xstandard extends JPlugin {
	
	/**
	 * Constructor
	 * 
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 * 
	 * @param object $subject The object to observe
	 * @since 1.1
	 */
	function JEditor_xstandard(& $subject) {
		parent::__construct($subject);
	}
	
	/**
	 * Method to handle the onInitEditor event.
	 *  - Initializes the XStandard Lite WYSIWYG Editor
	 * 
	 * @access public
	 * @return string JavaScript Initialization string
	 * @since 1.1
	 */
	function onInitEditor() 
	{
		global $mainframe;
		
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();
		
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
	 * XStandard Lite WYSIWYG Editor - copy editor contents to form field
	 * 
	 * @param string The name of the editor area
	 * @param string The name of the form field
	 */
	function onGetEditorContents( $editorArea, $hiddenField ) {

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
	function onEditorArea( $name, $content, $hiddenField, $width, $height, $col, $row ) 
	{
		global $mainframe;
		
 		$browser =& $mainframe->getBrowser();
		
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();

		$html = '';
		ob_start();
		?> 
	
		<div style="border: 1px solid #D5D5D5">  
		<object type="application/x-xstandard" id="<?php echo $name ?>" class="<?php echo $hiddenField ?>" width="<?php echo $width ?>" height="<?php echo $height ?>">
 			<param name="Value" value="<?php echo $content ?>" />
 			
 			<param name="Lang" value="en" />
			<param name="EnablePasteMarkup" value="yes" />
			<param name="EnableTimestamp" value="no" />
			<param name="Options" value="32768" />
			<param name="LatestVersion" value="1.7.0.0" />
 			
 			<param name="BorderColor" value="#FFF" />
 			<param name="Base" value="<?php echo $url ?>" />
 			<param name="License" value="XStandard Lite for Joomla!" />
 			
 			<param name="CMSCode" value="065126D6-357D-46FC-AF74-A1F5B2D5036E" />
 			<param name="CMSImageLibraryURL" value="<?php echo $url ?>plugins/editors/xstandard/imagelibrary.php" />
			<param name="CMSAttachmentLibraryURL" value="<?php echo $url ?>plugins/editors/xstandard/attachmentlibrary.php" />
		
			<textarea name="alternate1" id="alternate1" cols="60" rows="15"><?php echo $content ?></textarea>
		</object>
 		<input type="hidden" id="<?php echo $hiddenField ?>" name="<?php echo $hiddenField ?>" value="" />
 		</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
	
		return $html;
	}
}
?>