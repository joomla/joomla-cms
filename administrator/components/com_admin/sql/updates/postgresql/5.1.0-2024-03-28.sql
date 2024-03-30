--
-- Add post-installation message about Brotli compression in .htaccess
--
INSERT INTO "#__postinstall_messages" ("extension_id", "title_key", "description_key", "action_key", "language_extension", "language_client_id", "type", "action_file", "action", "condition_file", "condition_method", "version_introduced", "enabled")
SELECT "extension_id", 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_BROTLI_TITLE', 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_BROTLI_DESCRIPTION', '', 'com_admin', 1, 'message', '', '', 'admin://components/com_admin/postinstall/htaccessbrotli.php', 'admin_postinstall_htaccessbrotli_condition', '5.1.0', 1
  FROM "#__extensions"
 WHERE "name" = 'files_joomla'
   AND (SELECT COUNT(a."postinstall_message_id") FROM "#__postinstall_messages" a WHERE a."title_key" = 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_BROTLI_TITLE') = 0
ON CONFLICT DO NOTHING;
