-- From 4.0.0-2019-05-05.sql
UPDATE "#__menu" SET "link"='index.php?option=com_banners&view=banners' WHERE "menutype"='main' AND "path"='Banners/Banners';
UPDATE "#__menu" SET "link"='index.php?option=com_categories&view=categories&extension=com_banners' WHERE "menutype"='main' AND "path"='Banners/Categories';
UPDATE "#__menu" SET "link"='index.php?option=com_contact&view=contacts' WHERE "menutype"='main' AND "path"='Contacts/Contacts';
UPDATE "#__menu" SET "link"='index.php?option=com_categories&view=categories&extension=com_contact' WHERE "menutype"='main' AND "path"='Contacts/Categories';
UPDATE "#__menu" SET "link"='index.php?option=com_newsfeeds&view=newsfeeds' WHERE "menutype"='main' AND "path"='News Feeds/Feeds';
UPDATE "#__menu" SET "link"='index.php?option=com_categories&view=categories&extension=com_newsfeeds' WHERE "menutype"='main' AND "path"='News Feeds/Categories';
UPDATE "#__menu" SET "link"='index.php?option=com_redirect&view=links' WHERE "menutype"='main' AND "path"='Redirect';
UPDATE "#__menu" SET "link"='index.php?option=com_search&view=searches' WHERE "menutype"='main' AND "path"='Basic Search';
UPDATE "#__menu" SET "link"='index.php?option=com_finder&view=index' WHERE "menutype"='main' AND "path"='Smart Search';
UPDATE "#__menu" SET "link"='index.php?option=com_tags&view=tags' WHERE "menutype"='main' AND "path"='Tags';
UPDATE "#__menu" SET "link"='index.php?option=com_associations&view=associations' WHERE "menutype"='main' AND "path"='Multilingual Associations';

-- From 4.0.0-2019-05-20.sql
-- The following statement was modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE "#__extensions" ADD COLUMN "note" character varying(255) /** CAN FAIL **/;
