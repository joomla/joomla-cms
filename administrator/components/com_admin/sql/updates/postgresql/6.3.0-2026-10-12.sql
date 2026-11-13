INSERT INTO "#__action_log_config" ("type_title", "type_alias", "id_holder", "title_holder", "table_name", "text_prefix")
SELECT 'media_folder', 'com_media.folder', '', 'name', '', 'PLG_ACTIONLOG_JOOMLA'
 WHERE NOT EXISTS (SELECT 1 FROM "#__action_log_config" WHERE "type_alias" = 'com_media.folder');
