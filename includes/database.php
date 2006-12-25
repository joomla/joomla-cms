<?php // compatibility
/**
 * @package Joomla
 * @deprecated As of version 1.5
 */
require_once( dirname(__FILE__)  .'/../libraries/loader.php' );

jimport( 'joomla.database.database' );
jimport( 'joomla.database.database.mysql' );
/**
 * Legacy class, derive from JDatabase instead.
 *
 * @package Joomla
 * @deprecated As of version 1.5
 */
class database extends JDatabase {
	function __construct ($host='localhost', $user, $pass, $db='', $table_prefix='', $offline = true) {
		parent::__construct( 'mysql', $host, $user, $pass, $db, $table_prefix );
	}
}
?>
