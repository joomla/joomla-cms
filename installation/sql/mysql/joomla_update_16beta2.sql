
#
# Database updates for 1.6 Beta 1 to Beta 2
#

ALTER TABLE `#__redirect_links`
 CHANGE `created_date` `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `#__redirect_links`
 CHANGE `updated_date` `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `#__redirect_links`
 DROP INDEX `idx_link_updated`;

ALTER TABLE `#__redirect_links`
 ADD INDEX `idx_modified_date` (`modified_date`);

ALTER TABLE `#__categories` 
 CHANGE `created_time` `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `#__categories` 
 CHANGE `modified_time` `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
