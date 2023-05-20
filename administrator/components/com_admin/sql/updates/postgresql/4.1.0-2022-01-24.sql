DROP INDEX IF EXISTS "#__redirect_links_idx_link_modifed";
-- The following statement was modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
CREATE INDEX "#__redirect_links_idx_link_modified" ON "#__redirect_links" ("modified_date") /** CAN FAIL **/;
