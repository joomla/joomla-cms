DROP INDEX IF EXISTS "#__template_styles_idx_home";
# Queries removed, see https://github.com/joomla/joomla-cms/pull/25484
CREATE INDEX "#__template_styles_idx_client_id" ON "#__template_styles" ("client_id");
# Query removed, see https://github.com/joomla/joomla-cms/pull/25484
