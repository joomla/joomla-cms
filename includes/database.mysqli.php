<?php // compatibility
require_once( dirname(__FILE__)  .'/../libraries/loader.php' );

/**
* Legacy class, derive from JModel instead
* @deprecated As of version 1.1
*/
jimport( 'joomla.database.database' );
jimport( 'joomla.database.database.mysqli' );
/**
 * @package Joomla
 * @deprecated As of version 1.1
 */
class database extends JDatabaseMySQLi {
	function __construct ($host='localhost', $user, $pass, $db='', $table_prefix='', $offline = true) {
		parent::__construct( $host, $user, $pass, $db, $table_prefix );
	}
}
?>