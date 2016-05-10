INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(454, 'plg_system_stats', 'plugin', 'stats', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

INSERT INTO "#__postinstall_messages" ("extension_id", "title_key", "description_key", "action_key", "language_extension", "language_client_id", "type", "action_file", "action", "condition_file", "condition_method", "version_introduced", "enabled") VALUES
(700, 'COM_CPANEL_MSG_STATS_COLLECTION_TITLE', 'COM_CPANEL_MSG_STATS_COLLECTION_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/statscollection.php', 'admin_postinstall_statscollection_condition', '3.5.0', 1);
