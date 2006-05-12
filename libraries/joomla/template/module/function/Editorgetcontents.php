<?php
/**
 * @version $Id$
 * @package JamboWorks
 * @subpackage JamboZine
 * @copyright 2006 JamboWorks LLC. All rights reserved.
 * @license See LICENSE.TXT
 */

class patTemplate_Function_Editorgetcontents extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Editorgetcontents';

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
		// <mos:EditorGetContents name="body" />
		$name	= @$params['name'];

		jimport( 'joomla.presentation.editor' );
		$editor =& JEditor::getInstance();

		return $editor->getContent( $name );
	}
}
?>