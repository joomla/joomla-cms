<?php
/**
* @version $Id: admin.cachemanager.html.php 137 2005-09-12 10:21:17Z eddieajau $
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

class cacheManagerScreens {

	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {

		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* @param array
	*/
	function viewCache( $option, &$rows, &$pageNav ) {
		global $mosConfig_lang, $_LANG;

		mosCommonHTML::loadOverlib();

		$tmpl =& cacheManagerScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmplName = "comCacheManager";

		$tmpl->addVar( $tmplName, 'option', $option );
		$tmpl->addVar( $tmplName, 'action', 'index2.php' );
		$tmpl->addVar( $tmplName, 'method', 'post' );
		$tmpl->addVar( $tmplName, 'formname', 'adminForm' );
		$tmpl->addVar( $tmplName, 'formclass', 'adminform' );
		$tmpl->addVar( $tmplName, 'formid', 'cachemanagerform' );

		$pagenav = $pageNav->getPagesLinks();
		$limitbox = $_LANG->_( 'Display Num' )." ".$pageNav->getLimitBox().$pageNav->getPagesCounter();

		$tmpl->addVar( $tmplName, 'pagenav', $pagenav );
		$tmpl->addVar( $tmplName, 'limitbox', $limitbox );

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );

		$tmpl->displayParsedTemplate( $tmplName );
	}
}
?>