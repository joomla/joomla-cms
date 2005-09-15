<?php
/**
* @version $Id: weblinks.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Weblinks
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Mambo
 * @subpackage Weblinks
 */
class weblinksScreens {
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

	function displayList( &$params, &$current, &$rows, &$cats, &$vars ) {
		global $_MAMBOTS, $mainframe;
		global $Itemid;

		// SEO Meta Tags
		$mainframe->setPageMeta(
			$params->get( 'seo_title' ),
			$params->get( 'meta_key' ),
			$params->get( 'meta_descrip' )
		);

		// process the new bots
		$temp = new stdClass();
		$temp->text = $params->get( 'description_text' );
		$_MAMBOTS->loadBotGroup( 'content' );
		$results = $_MAMBOTS->trigger( 'onPrepareContent', array( &$temp, &$params ), true );
		$params->set( 'description_text', $temp->text );

		$tmpl =& weblinksScreens::createTemplate( 'list.html' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );
		$tmpl->addObject( 'body', $current, 'cur_' );

		// category list params
		$tmpl->addObject( 'categories', $cats, 'cat_' );

		// table links params
		$tmpl->addObject( 'rows', $rows, 'row_' );
		$vars['hastable'] = intval( count( $rows ) > 0 );

		$results = $_MAMBOTS->trigger( 'onAfterDisplayContent', array( &$current, &$params ) );
		$vars['onAfterDisplayContent'] = trim( implode( "\n", $results ) );

		$tmpl->addVars( 'body', $vars );
		$tmpl->addVar( 'form', 'formAction', sefRelToAbs( 'index.php?option=com_weblinks&amp;catid='. $vars['catid'] .'&amp;Itemid='. $Itemid ) );

		$tmpl->displayParsedTemplate( 'form' );
	}

	function edit( &$row, &$lists, $params ) {
		global $mainframe;

		$params = new mosParameters( '' );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );

		$toolbar = mosToolBar_return::startTable();
		$toolbar .= mosToolBar_return::save();
		$toolbar .= mosToolBar_return::cancel();
		$toolbar .= mosToolBar_return::endTable();

		$tmpl =& weblinksScreens::createTemplate( 'edit.html' );

		$tmpl->addVar( 'body', 'return', 		$lists['return'] );
		$tmpl->addVar( 'body', 'category', 		$lists['catid'] );
		$tmpl->addVar( 'body', 'toolbar', 		$toolbar );
		$tmpl->addVar( 'body', 'form_url', 		sefRelToAbs( 'index.php' ) );

		$tmpl->addVar( 'body', 'params',		$params->render( 'params', 0 ) );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->addObject( 'rows', $row, 'row_' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>