<?php
/**
 * @version $Id: export.class.php 137 2005-09-12 10:21:17Z eddieajau $
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

/**
 * @package Joomla
 * @subpackage Export
 */
class ExportFactory {

}


function populateDatabase( $sqlfile, &$errors, $debug=false ) {
	global $database;

	set_time_limit( 120 );
	$buffer = file_get_contents( dirname( __FILE__ ) . '/files/' . $sqlfile );
	$queries = splitSql( $buffer );

	foreach ($queries as $query) {
		$query = trim( $query );

		if ($query != '' && $query{0} != '#') {
			$database->setQuery( $query );
			if ($debug) {
				echo '<pre>'.$database->getQuery().'</pre>';
			}
			$database->query();
			if ($database->getErrorNum() > 0) {
				$errors[] = array (
					'msg' => $database->getErrorMsg(),
					'sql' => $query
				);
			}
		}
	}

	return count( $errors );
}

/**
 * @param string
 * @return array
 */
function splitSql( $sql ) {
	$sql = trim( $sql );
	$sql = preg_replace( "/\n\#[^\n]*/", '', "\n" . $sql );

	$buffer = array();
	$ret = array();
	$in_string = false;

	for ($i = 0; $i < strlen( $sql )-1; $i++) {

		if($sql[$i] == ";" && !$in_string) {
			$ret[] = substr($sql, 0, $i);
			$sql = substr($sql, $i + 1);
			$i = 0;
		}

		if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
			$in_string = false;
		} else if(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
			$in_string = $sql[$i];
		}

		if (isset( $buffer[1] )) {
			$buffer[0] = $buffer[1];
		}

		$buffer[1] = $sql[$i];
	}

	if(!empty($sql)) {
		$ret[] = $sql;
	}

	return($ret);
}
?>