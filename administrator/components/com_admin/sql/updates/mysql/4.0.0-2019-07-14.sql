ALTER TABLE `#__contact_details` DROP COLUMN `xreference`;
ALTER TABLE `#__content` DROP COLUMN `xreference`;
ALTER TABLE `#__newsfeeds` DROP COLUMN `xreference`;

ALTER TABLE `#__content_frontpage` ADD COLUMN `featured_up` datetime;
ALTER TABLE `#__content_frontpage` ADD COLUMN `featured_down` datetime;
