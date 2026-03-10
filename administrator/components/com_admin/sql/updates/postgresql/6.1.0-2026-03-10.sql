-- --------------------------------------------------------
-- The following statement replaces the statement which has been disabled
-- in file "6.1.0-2026-01-29.sql" with 6.1.0-beta3.
-- See https://github.com/joomla/joomla-cms/pull/47361 for details.
--
INSERT INTO "#__content_types" ("type_title", "type_alias", "table", "rules", "field_mappings", "router", "content_history_options")
SELECT 'Module', 'com_modules.module', '{"special":{"dbtable":"#__modules","key":"id","type":"Module","prefix":"Joomla\\\\CMS\\\\Table\\\\"}}', '', '{}', ''
     , '{"formFile":"administrator\\/components\\/com_modules\\/forms\\/module.xml", "hideFields":["checked_out", "checked_out_time", "publish_up", "publish_down"], "ignoreChanges":["checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"checked_out", "targetTable":"#__users", "targetColumn":"id", "displayColumn":"name"}]}'
WHERE NOT EXISTS (
  SELECT * FROM "#__content_types" c
   WHERE c."type_title" = 'Module' AND c."type_alias" = 'com_modules.module'
     AND c."table" = '{"special":{"dbtable":"#__modules","key":"id","type":"Module","prefix":"Joomla\\\\CMS\\\\Table\\\\"}}'
);
