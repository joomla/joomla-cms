ALTER TABLE `#__languages` MODIFY `asset_id` int unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__menu_types` MODIFY `asset_id` int unsigned NOT NULL DEFAULT 0;

ALTER TABLE  `#__content` MODIFY `xreference` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE  `#__newsfeeds` MODIFY `xreference` varchar(50) NOT NULL DEFAULT '';
