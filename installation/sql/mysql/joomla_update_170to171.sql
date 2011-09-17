# $Id$

#
# Database updates for 1.7.0 to 1.7.1
#
ALTER TABLE #__categories MODIFY description MEDIUMTEXT;
ALTER TABLE #__session MODIFY data MEDIUMTEXT;
ALTER TABLE #__session MODIFY session_id varchar(200);

# Add new module to extensions table
REPLACE INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(313, 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_multilangstatus","type":"module","creationDate":"September 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"MOD_MULTILANGSTATUS_XML_DESCRIPTION","group":""}', '{"cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

# Move User Status module to order 2
UPDATE #__modules
SET `ordering` = 2
WHERE `id` = 14 AND `title` = 'User Status';

# Add new module to modules table as unpublished
# Use NULL for id to get next available id
INSERT INTO `#__modules` (`id`, `title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
(NULL, 'Multilanguage status', '', '', 1, 'status', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*');

# Add new module to module menu mapping table
# Use LAST_INSERT_ID() to get new module id
INSERT INTO `#__modules_menu`
SET `moduleid` = LAST_INSERT_ID(), `menuid` = 0;

# Alter module table to cope with the new non-required position field
ALTER TABLE `#__modules` CHANGE `position` `position` VARCHAR( 50 ) NOT NULL DEFAULT ''
