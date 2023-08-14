ALTER TABLE `#__guidedtours` ADD COLUMN `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL AFTER `title`/** CAN FAIL **/;
ALTER TABLE `#__guidedtours` ADD INDEX `idx_alias` (`alias`(191)) /** CAN FAIL **/;

UPDATE `#__guidedtours` SET `alias` = 'joomla_guidedtours' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS';
UPDATE `#__guidedtours` SET `alias` = 'joomla_guidedtourssteps' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS';
UPDATE `#__guidedtours` SET `alias` = 'joomla_articles'  WHERE `title` = 'COM_GUIDEDTOURS_TOUR_ARTICLES';
UPDATE `#__guidedtours` SET `alias` = 'joomla_categories' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_CATEGORIES';
UPDATE `#__guidedtours` SET `alias` = 'joomla_menus' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_MENUS';
UPDATE `#__guidedtours` SET `alias` = 'joomla_tags' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_TAGS';
UPDATE `#__guidedtours` SET `alias` = 'joomla_banners' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_BANNERS';
UPDATE `#__guidedtours` SET `alias` = 'joomla_contacts' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_CONTACTS';
UPDATE `#__guidedtours` SET `alias` = 'joomla_newsfeeds' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS';
UPDATE `#__guidedtours` SET `alias` = 'joomla_smartsearch' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH';
UPDATE `#__guidedtours` SET `alias` = 'joomla_users' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_USERS';
