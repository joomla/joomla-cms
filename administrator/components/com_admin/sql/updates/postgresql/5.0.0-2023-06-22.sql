ALTER TABLE "#__guidedtours" ADD COLUMN "alias" varchar(255) DEFAULT '' NOT NULL /** CAN FAIL **/;
CREATE INDEX "#__guidedtours_idx_alias" ON "#__guidedtours" ("alias") /** CAN FAIL **/;

UPDATE "#__guidedtours" SET "alias" = 'joomla_guidedtours_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_guidedtourssteps_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_articles_title'  WHERE "title" = 'COM_GUIDEDTOURS_TOUR_ARTICLES_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_categories_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_CATEGORIES_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_menus_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_MENUS_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_tags_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_TAGS_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_banners_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_BANNERS_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_contacts_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_CONTACTS_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_newsfeeds_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_smartsearch_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_TITLE';
UPDATE "#__guidedtours" SET "alias" = 'joomla_users_title' WHERE "title" = 'COM_GUIDEDTOURS_TOUR_USERS_TITLE';
