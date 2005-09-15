<?php
/**
 * @version $Id: dbtable.php 137 2005-09-12 10:21:17Z eddieajau $
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

$exportFormatters['class'] = 'dbTableFormatter';

class dbTableFormatter {
	function options( &$tmpl ) {
		$tmpl->readTemplatesFromInput( '../formatters/dbtable.html' );
		return $tmpl->getParsedTemplate( 'dbtable-formatter-options' );
	}

	/**
	 * Creates an XMI document that can be imported into Visual Paradigm
	 */
	function export( &$tables, &$table_fields, &$table_creates, &$options ) {
		$buffer = '';
		foreach ($tables as $table) {
			$buffer .= $this->_createClass( $table, $table_fields[$table] );
		}
		return $buffer;
	}

	function _createClass( &$table, &$table_fields ) {
		global $database;

		$tableName = str_replace( $database->getPrefix(), '', $table );
		$className = 'mos' . ucfirst( strtolower( $tableName ) );
		$buffer = "\n/**";
		$buffer .= "\n* Class $className";
		$buffer .= "\n*/";
		$buffer .= "\nclass $className extends mosDBTable {";
		foreach ($table_fields as $k=>$v) {
			$buffer .= "\n/** @var $v */";
			$buffer .= "\n	var \$$k;";
		}
		$buffer .= "\n\n	function $className() {";
		$buffer .= "\n		global \$database;";
		$buffer .= "\n		\$this->mosDBTable( '#__$tableName', 'id', \$database );";
		$buffer .= "\n	}";
		$buffer .= "\n}\n";

		return $buffer;
	}
}
?>