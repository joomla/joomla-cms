<?php
define( '_JEXEC', 1); 
define( 'JPATH_BASE', realpath(dirname(__FILE__) .'/' ) );
require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );
$db = JFactory::getDBO();
$cf=$db->getTableColumns('#__content'); // It returns array with fields


/** In database
create schema test;
CREATE TABLE test.customer (
  id SERIAL,
  name VARCHAR(100),
  PRIMARY KEY(id)
) 
WITH (oids = false);
*/

$custfields=$db->getTableColumns('test.customer'); // It returns empty array
var_dump($custfields);

?>