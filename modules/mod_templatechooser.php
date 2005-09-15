<?php
/**
* @version $Id: mod_templatechooser.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class modTemplatechooserData {

	function &getParams( &$params ) {
		global $mainframe;
		global $_LANG, $mosConfig_absolute_path;

		$titlelength 	= $params->def( 'title_length', 20 );
		$show_preview 	= $params->def( 'show_preview', 0 );

		// Read files from template directory
		$template_path 	= $mosConfig_absolute_path .'/templates';
		$templatefolder = @dir( $template_path );
		$darray 		= array();

		if ( $templatefolder ) {
			while ( $templatefile = $templatefolder->read() ) {
				if ( $templatefile != '.' && $templatefile != '..' && $templatefile != 'CVS' && is_dir( $template_path .'/'. $templatefile )  ) {
					if( strlen( $templatefile ) > $titlelength ) {
						$templatename = substr( $templatefile, 0, $titlelength - 3 );
						$templatename .= '...';
					} else {
						$templatename = $templatefile;
					}
					$darray[] = mosHTML::makeOption( $templatefile, $templatename );
				}
			}
			$templatefolder->close();
		}

		$cur_template = $params->def( 'template', $mainframe->getTemplate() );
		sort( $darray );
		// Show the preview image
		if( $show_preview ) {
			$onchange = 'showimage()';
		} else {
			$onchange = '';
		}
		$list = mosHTML::selectList( $darray, 'mos_change_template', 'class="button" onchange="'. $onchange .'" onkeyup="'. $onchange .'"', 'value', 'text', $cur_template );
		$params->set( 'dropdown', $list );

		return $params;
	}
}


class modTemplatechooser {

	function show ( &$params ) {
		modTemplatechooser::_display($params);
	}

	function _display( &$params ) {

		$params = modTemplatechooserData::getParams( $params );

		$tmpl =& moduleScreens::createTemplate( 'mod_templatechooser.html' );

		$tmpl->addVar( 'mod_templatechooser', 'class', 	$params->get( 'moduleclass_sfx' ) );
		$tmpl->addVar( 'mod_templatechooser', 'url', ampReplace( $_SERVER['REQUEST_URI'] ) );

		$tmpl->addObject( 'mod_templatechooser', $params->toObject() );

		$tmpl->displayParsedTemplate( 'mod_templatechooser' );
	}
}

modTemplatechooser::show( $params );
?>