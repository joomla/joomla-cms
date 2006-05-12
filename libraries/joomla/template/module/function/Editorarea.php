<?php
/**
 * @version $Id$
 * @package JamboWorks
 * @subpackage JamboZine
 * @copyright 2006 JamboWorks LLC. All rights reserved.
 * @license See LICENSE.TXT
 */

class patTemplate_Function_Editorarea extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Editorarea';

   /**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	* @author	Andrew Eddie
	* Function modifed for Joomla!
	*/
	function call( $params, $content )
	{
		// <mos:EditorArea name="body" width="95%" height="350" col="75", row="20">{ITEM_BODY|htmlscpecialchars}</mos:EditorArea>

		$name	= @$params['name'];
		$width	= @$params['width'];
		$height	= @$params['height'];
		$col	= @$params['col'];
		$row	= @$params['row'];

		jimport( 'joomla.presentation.editor' );
		$editor =& JEditor::getInstance();

		return $editor->display( $name, $content, $width, $height, $col, $row ) ;
	}
}
?>