--
-- Add post-installation message about .htaccess hardening for media directories
--
INSERT INTO "#__postinstall_messages" ("extension_id", "title_key", "description_key", "action_key", "language_extension", "language_client_id", "type", "action_file", "action", "condition_file", "condition_method", "version_introduced", "enabled")
SELECT "extension_id", 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_MEDIA_TITLE', 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_MEDIA_DESCRIPTION', '', 'com_admin', 1, 'message', '', '', 'admin://components/com_admin/postinstall/htaccessmedia.php', 'admin_postinstall_htaccessmedia_condition', '6.2.0', 1 FROM "#__extensions" WHERE "name" = 'files_joomla';
