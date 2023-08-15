ALTER TABLE `#__guidedtours` ADD COLUMN `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL AFTER `title`/** CAN FAIL **/;
ALTER TABLE `#__guidedtours` ADD INDEX `idx_alias` (`alias`(191)) /** CAN FAIL **/;

UPDATE `#__guidedtours` SET `alias` = 'joomla_guidedtours' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_guidedtourssteps' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_articles'  WHERE `title` = 'COM_GUIDEDTOURS_TOUR_ARTICLES_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_categories' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_CATEGORIES_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_menus' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_MENUS_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_tags' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_TAGS_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_banners' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_BANNERS_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_contacts' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_CONTACTS_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_newsfeeds' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_smartsearch' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_TITLE';
UPDATE `#__guidedtours` SET `alias` = 'joomla_users' WHERE `title` = 'COM_GUIDEDTOURS_TOUR_USERS_TITLE';