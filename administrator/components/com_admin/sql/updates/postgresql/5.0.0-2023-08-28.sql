
UPDATE "#__guidedtours" SET "extensions" = '["com_content","com_categories"]' WHERE "url" LIKE '%option=com_content%';
UPDATE "#__guidedtours" SET "extensions" = '["com_content","com_categories"]' WHERE "url" LIKE '%option=com_categories%';
UPDATE "#__guidedtours" SET "extensions" = '["com_menus"]' WHERE "url" LIKE '%com_menus%';
UPDATE "#__guidedtours" SET "extensions" = '["com_tags"]' WHERE "url" LIKE '%com_tags%';
UPDATE "#__guidedtours" SET "extensions" = '["com_banners"]' WHERE "url" LIKE '%com_banners%';
UPDATE "#__guidedtours" SET "extensions" = '["com_contact"]' WHERE "url" LIKE '%com_contact%';
UPDATE "#__guidedtours" SET "extensions" = '["com_newsfeeds"]' WHERE "url" LIKE '%com_newsfeeds%';
UPDATE "#__guidedtours" SET "extensions" = '["com_finder"]' WHERE "url" LIKE '%com_finder%';
UPDATE "#__guidedtours" SET "extensions" = '["com_users"]' WHERE "url" LIKE '%com_users%';

UPDATE "#__update_sites"
   SET "location" = 'https://update.joomla.org/language/translationlist_5.xml'
 WHERE "location" = 'https://update.joomla.org/language/translationlist_4.xml';
