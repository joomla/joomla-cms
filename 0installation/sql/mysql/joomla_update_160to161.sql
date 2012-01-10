# $Id: joomla_update_160to161.sql 20466 2011-01-28 00:16:31Z dextercowley $

#
# Database updates for 1.6.0 to 1.6.1
#

REPLACE INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
 (103, 'Joomla! Web Application Framework', 'library', 'joomla', '', 0, 1, 1, 1, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:33:"Joomla! Web Application Framework";s:4:"type";s:7:"library";s:12:"creationDate";s:4:"2008";s:6:"author";s:6:"Joomla";s:9:"copyright";s:67:"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.";s:11:"authorEmail";s:16:"admin@joomla.org";s:9:"authorUrl";s:21:"http://www.joomla.org";s:7:"version";s:5:"1.6.0";s:11:"description";s:90:"The Joomla! Web Application Framework is the Core of the Joomla! Content Management System";s:5:"group";s:0:"";}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);
 