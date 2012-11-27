TRUNCATE TABLE `#__update_sites`;
INSERT INTO `#__update_sites` (`update_site_id`,`name`,`type`,`location`,`enabled`,`last_check_timestamp`) VALUES 
(1,'Jokte Extensiones','collection','http://update.jokte.org/list.xml',1,0), 
(2,'Jokte Core','collection','http://update.jokte.org/core/list.xml',1,0), 
(3,'Traducciones Jokte Oficiales','collection','http://update.jokte.org/lenguajes/translationlist.xml',1,0);

