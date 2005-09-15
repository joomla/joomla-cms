<?php
/**
 * @version $Id: axmls.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Joomla
 * @subpackage Export
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$exportFormatters['axmls'] = 'axmlsFormatter';

class axmlsFormatter {
	function options( &$tmpl ) {
		$tmpl->readTemplatesFromInput( '../formatters/axmls.html' );
		return $tmpl->getParsedTemplate( 'axmls-formatter-options' );
	}

	/**
	 * Creates an XMI document that can be imported into Visual Paradigm
	 */
	function export( &$tables, &$table_fields, &$table_creates, &$options ) {
		global $database;

		$source = mosGetParam( $options, 'source', '' );
		$sourceStructure = eregi( 's', $source );
		$sourceData = eregi( 'd', $source );

		mosFS::load( 'includes/adodb/adodb-xmlschema.inc.php' );
		$schema = new adoSchema( $database->_resource );
		$xml = $schema->ExtractSchema( $sourceData, $tables );
		$xml = str_replace( '<table name="' . $database->getPrefix(), '<table name="', $xml );
		return $xml;
	}

}


?>