# Placeholder file for database changes for version 3.0.0
ALTER TABLE `#__contact_details` DROP `imagepos`;
ALTER TABLE `#__content` DROP `title_alias`, DROP `sectionid`, DROP `mask`, DROP `parentid`;
ALTER TABLE `#__newsfeeds` DROP `filename`;
ALTER TABLE `#__weblinks` DROP `sid`, DROP `date`, DROP `archived`, DROP `approved`;
ALTER TABLE `#__menu` DROP `ordering`;

ALTER TABLE `#__weblinks`   ADD   `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__weblinks`   ADD   `images` text NOT NULL;
 

ALTER TABLE `#__newsfeeds`  ADD    `description` text NOT NULL;
ALTER TABLE `#__newsfeeds`   ADD   `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__newsfeeds`   ADD   `hits` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__newsfeeds`   ADD   `images` text NOT NULL;

ALTER TABLE `#__contact_details`   ADD   `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__contact_details`   ADD   `hits` int(10) unsigned NOT NULL DEFAULT '0';


ALTER TABLE `#__banners`  ADD      `created_by` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__banners`  ADD     `created_by_alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__banners`  ADD     `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__banners`  ADD     `modified_by` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__banners`   ADD   `version` int(10) unsigned NOT NULL DEFAULT '1';


ALTER TABLE `#__categories`   ADD   `version` int(10) unsigned NOT NULL DEFAULT '1';
