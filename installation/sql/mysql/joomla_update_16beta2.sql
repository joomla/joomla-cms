# $Id: joomla.sql 17225 2010-05-24 03:01:15Z dextercowley $

#
# Database updates for 1.6 Beta 1 to Beta 2
#

ALTER TABLE `jos_redirect_links`
 CHANGE `created_date` `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `jos_redirect_links`
 CHANGE `updated_date` `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `jos_redirect_links`
 DROP INDEX `idx_link_updated`;

ALTER TABLE `jos_redirect_links`
 ADD INDEX `idx_modified_date` (`modified_date`);
