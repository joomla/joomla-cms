DROP INDEX IF EXISTS "#__template_styles_idx_home";
CREATE INDEX "#__template_styles_idx_client_id" ON "#__template_styles" ("client_id");
# Queries removed, see https://github.com/joomla/joomla-cms/pull/XXXXXX
