ALTER TABLE `#__guidedtours` ADD COLUMN `uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL AFTER `title`/** CAN FAIL **/;
ALTER TABLE `#__guidedtours` ADD INDEX `idx_uid` (`uid`(191)) /** CAN FAIL **/;

UPDATE `#__guidedtours` SET `uid` = 'joomla-guidedtours' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-guidedtoursteps' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-articles'  WHERE `title` = 'COM_GUIDEDTOURS_TOUR_ARTICLES_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-categories' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_CATEGORIES_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-menus' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_MENUS_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-tags' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_TAGS_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-banners' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_BANNERS_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-contacts' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_CONTACTS_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-newsfeeds' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-smartsearch' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_TITLE';
UPDATE `#__guidedtours` SET `uid` = 'joomla-users' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_USERS_TITLE';
