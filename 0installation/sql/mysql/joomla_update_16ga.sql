# $Id: joomla_update_16ga.sql 20467 2011-01-28 00:17:12Z dextercowley $

#
# Database updates for 1.6 RC1 to 1.6 GA
#

REPLACE INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
 (500, 'atomic', 'template', 'atomic', '', 0, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:6:"atomic";s:4:"type";s:8:"template";s:12:"creationDate";s:8:"10/10/09";s:6:"author";s:12:"Ron Severdia";s:9:"copyright";s:72:"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:25:"contact@kontentdesign.com";s:9:"authorUrl";s:28:"http://www.kontentdesign.com";s:7:"version";s:5:"1.6.0";s:11:"description";s:26:"TPL_ATOMIC_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
 (501, 'rhuk_milkyway', 'template', 'rhuk_milkyway', '', 0, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:13:"rhuk_milkyway";s:4:"type";s:8:"template";s:12:"creationDate";s:8:"11/20/06";s:6:"author";s:11:"Andy Miller";s:9:"copyright";s:72:"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:20:"rhuk@rockettheme.com";s:9:"authorUrl";s:26:"http://www.rockettheme.com";s:7:"version";s:5:"1.6.0";s:11:"description";s:33:"TPL_RHUK_MILKYWAY_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{"colorVariation":"white","backgroundVariation":"blue","widthStyle":"fmax"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
 (502, 'bluestork', 'template', 'bluestork', '', 1, 1, 1, 1, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:9:"bluestork";s:4:"type";s:8:"template";s:12:"creationDate";s:8:"07/02/09";s:6:"author";s:12:"Ron Severdia";s:9:"copyright";s:72:"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:25:"contact@kontentdesign.com";s:9:"authorUrl";s:28:"http://www.kontentdesign.com";s:7:"version";s:5:"1.6.0";s:11:"description";s:29:"TPL_BLUESTORK_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{"useRoundedCorners":"1","showSiteName":"0","textBig":"0","highContrast":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
 (503, 'beez_20', 'template', 'beez_20', '', 0, 1, 1, 1, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:7:"beez_20";s:4:"type";s:8:"template";s:12:"creationDate";s:16:"25 November 2009";s:6:"author";s:12:"Angie Radtke";s:9:"copyright";s:72:"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:23:"a.radtke@derauftritt.de";s:9:"authorUrl";s:26:"http://www.der-auftritt.de";s:7:"version";s:5:"1.6.0";s:11:"description";s:25:"TPL_BEEZ2_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
 (504, 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:6:"hathor";s:4:"type";s:8:"template";s:12:"creationDate";s:8:"May 2010";s:6:"author";s:11:"Andrea Tarr";s:9:"copyright";s:72:"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:25:"hathor@tarrconsulting.com";s:9:"authorUrl";s:29:"http://www.tarrconsulting.com";s:7:"version";s:5:"1.6.0";s:11:"description";s:26:"TPL_HATHOR_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
 (505, 'beez5', 'template', 'beez5', '', 0, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:5:"beez5";s:4:"type";s:8:"template";s:12:"creationDate";s:11:"21 May 2010";s:6:"author";s:12:"Angie Radtke";s:9:"copyright";s:72:"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:23:"a.radtke@derauftritt.de";s:9:"authorUrl";s:26:"http://www.der-auftritt.de";s:7:"version";s:5:"1.6.0";s:11:"description";s:25:"TPL_BEEZ5_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","html5":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);
 
DELETE FROM `#__extensions` WHERE `extension_id`=103;

REPLACE INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
 (102, 'phputf8', 'library', 'phputf8', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

# 2011-01-05: Alteration for issue 21275.

ALTER TABLE `#__banner_tracks`
 CHANGE `track_date` `track_date` datetime NOT NULL;

# Add missing Joomla content plugin.

REPLACE INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(435, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, '', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);


# 2010-01-08: Params field changes for issue 24013
ALTER TABLE `#__categories` MODIFY COLUMN params TEXT;
ALTER TABLE `#__modules` MODIFY COLUMN params TEXT;
ALTER TABLE `#__template_styles` MODIFY COLUMN params TEXT;
ALTER TABLE `#__menu` MODIFY COLUMN params TEXT COMMENT 'JSON encoded data for the menu item.';
