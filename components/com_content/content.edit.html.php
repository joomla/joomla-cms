<?php
/**
* @version $Id: content.edit.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Content Edit
 */
class contentEditScreens_front {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml='', $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );

		$directory = mosComponentDirectory( $bodyHtml, dirname( __FILE__ ) );
		$tmpl->setRoot( $directory );

		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	/**
	* Writes the edit form for to edit an existing Category item
	*/
	function editContent( &$row, $task, &$lists, &$access, &$images  ) {
		global $Itemid;
		global $_LANG;

		$link = 'index.php?option=com_content&task='. $task .'&sectionid='. $lists['sectionid'] .'&id='. $row->id .'&Itemid='. $Itemid;
		$link = sefRelToAbs( $link );

	  	if ( $task == 'editpop' ) {
	  		$js_warning		= '';
	  		$width			= '700';
	  		$height			= '450';
	  		$rows			= '85';
	  		$cols			= '35';
	  	} else {
	  		$js_warning		= 'onunload = WarnUser;';
	  		$width			= '550';
	  		$height			= '400';
	  		$rows			= '65';
	  		$cols			= '30';
	  	}

		$editor1 		= editorArea( 'editor1',  $row->introtext , 'introtext', $width, $height, $rows, $cols, 1, 1 );
		$editor2 		= editorArea( 'editor2',  $row->fulltext , 'fulltext', $width, $height, $rows, $cols, 1, 1 );
		$get_editor1 	= getEditorContents( 'editor1', 'introtext', 1 );
		$get_editor2 	= getEditorContents( 'editor2', 'fulltext', 1 );

		$toolbar = mosToolBar_return::startTable();
		if ( $task == 'editpop' ) {
			$toolbar .= mosToolBar_return::save( 'savepop' );
			$toolbar .= mosToolBar_return::apply();
			$toolbar .= mosToolBar_return::cancel( 'cancelpop' );
		} else {
			$toolbar .= mosToolBar_return::save();
			$toolbar .= mosToolBar_return::apply();
			$toolbar .= mosToolBar_return::cancel();
		}
		$toolbar .= mosToolBar_return::endtable();

		// info for overlib
		$docinfo = '<table><tr><td>';
		$docinfo .= '<strong>'. $_LANG->_( 'E_EXPIRES' ). '</strong>';
		$docinfo .= '</td><td>';
		$docinfo .= $row->publish_down .'<br />';
		$docinfo .= '</td></tr>';
		$docinfo .= '<tr><td>';
		$docinfo .= '<strong>'. $_LANG->_( 'E_VERSION' ). '</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $row->version .'<br />';
		$docinfo .= '</td></tr>';
		$docinfo .= '<tr><td>';
		$docinfo .= '<strong>'. $_LANG->_( 'E_CREATED' ). '</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $row->created .'<br />';
		$docinfo .= '</td></tr>';
		$docinfo .= '<tr><td>';
		$docinfo .= '<strong>'. $_LANG->_( 'E_LAST_MOD' ). '</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $row->modified .'<br />';
		$docinfo .= '</td></tr>';
		$docinfo .= '<tr><td>';
		$docinfo .= '<strong>'. $_LANG->_( 'E_HITS' ). '</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $row->hits .'<br />';
		$docinfo .= '</td></tr></table>';

		$frontpage 	= ( $row->frontpage ? 'checked="checked"' : '' );
		$text 		= ( $row->id ? $_LANG->_( 'E_EDIT' ) : $_LANG->_( 'E_ADD' ) );

		$i = 0;
		foreach ( $images as $k=>$items ) {
			foreach ( $items as $v ) {
				$js_link[$i]->link = "\n	folderimages[".$i++."] = new Array( '$k','".addslashes( $v->value )."','".addslashes( $v->text )."' );";
			}
		}

		$tmpl =& contentEditScreens_front::createTemplate( 'edit_content.html' );

		$tmpl->addVar( 'body', 'toolbar', 		$toolbar );
		$tmpl->addVar( 'body', 'link', 			$link );
		$tmpl->addVar( 'body', 'js', 			$js_warning );
		$tmpl->addVar( 'body', 'overlib_info',	$docinfo );
		$tmpl->addVar( 'body', 'editor1', 		$editor1 );
		$tmpl->addVar( 'body', 'editor2', 		$editor2 );
		$tmpl->addVar( 'body', 'get_editor1', 	$get_editor1 );
		$tmpl->addVar( 'body', 'get_editor2', 	$get_editor2 );
		$tmpl->addVar( 'body', 'referer', 		@$_SERVER['HTTP_REFERER'] );
		$tmpl->addVar( 'body', 'frontpage',		$frontpage );
		$tmpl->addVar( 'body', 'static',		( $row->sectionid == 0 ? 1 : 0 ) );
		$tmpl->addVar( 'body', 'text',			$text );
		$tmpl->addVar( 'body', 'can_publish',	( $access->canPublish ? 1 : 0 ) );
		$tmpl->addVar( 'body', 'panecookies',	0 );
		$tmpl->addVar( 'body', 'paneid',		'content-pane' );

		$tmpl->addObject( 'js', $js_link, 'js_' );
		$tmpl->addObject( 'body', $row, 'row_' );

		$tmpl->addVar( 'body', 'list_state', 			$lists['state'] );
		$tmpl->addVar( 'body', 'list_access', 			$lists['access'] );
		$tmpl->addVar( 'body', 'list_ordering', 		$lists['ordering'] );
		$tmpl->addVar( 'body', 'list_catid', 			$lists['catid'] );
		$tmpl->addVar( 'body', 'list_folders', 			$lists['folders'] );
		$tmpl->addVar( 'body', 'list_imagefiles',		$lists['imagefiles'] );
		$tmpl->addVar( 'body', 'list_imagelist',		$lists['imagelist'] );
		$tmpl->addVar( 'body', 'list_align',			$lists['_align'] );
		$tmpl->addVar( 'body', 'list_caption_align',	$lists['_caption_align'] );
		$tmpl->addVar( 'body', 'list_caption_position',	$lists['_caption_position'] );
		$tmpl->addVar( 'body', 'list_link_target',		$lists['_link_target'] );
		$tmpl->addVar( 'body', 'section',				$lists['section'] );

		$tmpl->displayParsedTemplate( 'body' );
	}

	/**
	* Writes the edit form for to edit an existing Category item
	*/
	function editCategory( &$row, $task, &$lists  ) {
		global $Itemid;

		$link = 'index.php?option=com_content&task=edit_category&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid;
		$link = sefRelToAbs( $link );

		if ( $task == 'edit_categorypop' ) {
	  		$js_warning		= '';
	  		$width			= '700';
	  		$height			= '450';
	  		$rows			= '85';
	  		$cols			= '35';
	  	} else {
	  		$js_warning		= 'onunload = WarnUser;';
	  		$width			= '550';
	  		$height			= '400';
	  		$rows			= '65';
	  		$cols			= '30';
	  	}

		$editor 	= editorArea( 'editor1',  $row->description , 'description', $width, $height, $rows, $cols, 0, 1, 1 ) ;
		$get_editor = getEditorContents( 'editor1', 'description', 1 );

		$toolbar = mosToolBar_return::startTable();
		if ( $task == 'edit_categorypop' ) {
			$toolbar .= mosToolBar_return::save( 'save_categorypop' );
			$toolbar .= mosToolBar_return::apply( 'apply_categorypop' );
			$toolbar .= mosToolBar_return::cancel( 'cancel_categorypop' );
		} else {
			$toolbar .= mosToolBar_return::save( 'save_category' );
			$toolbar .= mosToolBar_return::apply( 'apply_category' );
			$toolbar .= mosToolBar_return::cancel( 'cancel_category' );
		}
		$toolbar .= mosToolBar_return::endtable();

		$tmpl =& contentEditScreens_front::createTemplate( 'edit_category.html' );

		$tmpl->addVar( 'body', 'toolbar', 		$toolbar );
		$tmpl->addVar( 'body', 'link', 			$link );
		$tmpl->addVar( 'body', 'js', 			$js_warning );
		$tmpl->addVar( 'body', 'editor', 		$editor );
		$tmpl->addVar( 'body', 'get_editor', 	$get_editor );
		$tmpl->addVar( 'body', 'referer', 		@$_SERVER['HTTP_REFERER'] );

		$tmpl->addObject( 'body', $row, 'row_' );

		$tmpl->addVar( 'body', 'list_state', 			$lists['state'] );
		$tmpl->addVar( 'body', 'list_access', 			$lists['access'] );
		$tmpl->addVar( 'body', 'list_image', 			$lists['image'] );
		$tmpl->addVar( 'body', 'list_image_position', 	$lists['image_position'] );

		$tmpl->displayParsedTemplate( 'body' );
	}

	/**
	* Writes the edit form for to edit an existing Section item
	*/
	function editSection( &$row, $task, &$lists  ) {
		global $Itemid;

		$link = 'index.php?option=com_content&task=edit_section&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid;
		$link = sefRelToAbs( $link );

		if ( $task == 'edit_sectionpop' ) {
	  		$js_warning		= '';
	  		$width			= '700';
	  		$height			= '450';
	  		$rows			= '85';
	  		$cols			= '35';
	  	} else {
	  		$js_warning		= 'onunload = WarnUser;';
	  		$width			= '550';
	  		$height			= '400';
	  		$rows			= '65';
	  		$cols			= '30';
	  	}

		$editor 	= editorArea( 'editor1',  $row->description , 'description', $width, $height, $rows, $cols, 0, 1, 1 ) ;
		$get_editor = getEditorContents( 'editor1', 'description', 1 );

		$toolbar = mosToolBar_return::startTable();
		if ( $task == 'edit_sectionpop' ) {
			$toolbar .= mosToolBar_return::save( 'save_sectionpop' );
			$toolbar .= mosToolBar_return::apply( 'apply_sectionpop');
			$toolbar .= mosToolBar_return::cancel( 'cancel_sectionpop' );
		} else {
			$toolbar .= mosToolBar_return::save( 'save_section' );
			$toolbar .= mosToolBar_return::apply( 'apply_section' );
			$toolbar .= mosToolBar_return::cancel( 'cancel_section' );
		}
		$toolbar .= mosToolBar_return::endtable();

		$tmpl =& contentEditScreens_front::createTemplate( 'edit_section.html' );

		$tmpl->addVar( 'body', 'toolbar', 		$toolbar );
		$tmpl->addVar( 'body', 'link', 			$link );
		$tmpl->addVar( 'body', 'js', 			$js_warning );
		$tmpl->addVar( 'body', 'editor', 		$editor );
		$tmpl->addVar( 'body', 'get_editor', 	$get_editor );

		$tmpl->addObject( 'body', $row, 'row_' );

		$tmpl->addVar( 'body', 'list_state', 			$lists['state'] );
		$tmpl->addVar( 'body', 'list_access', 			$lists['access'] );
		$tmpl->addVar( 'body', 'list_image', 			$lists['image'] );
		$tmpl->addVar( 'body', 'list_image_position', 	$lists['image_position'] );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>