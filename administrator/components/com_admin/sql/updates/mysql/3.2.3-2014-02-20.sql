UPDATE `#__extensions` ext1, `#__extensions` ext2 SET ext1.`params` =  ext2.`params` WHERE ext1.`name` = 'plg_authentication_cookie' AND ext2.`name` = 'plg_system_remember';
