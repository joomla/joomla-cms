--
-- Add post-installation message about the new support for JPEG XL images..
--
INSERT INTO "#__postinstall_messages" ("extension_id", "title_key", "description_key", "action_key", "language_extension", "language_client_id", "type", "action_file", "action", "condition_file", "condition_method", "version_introduced", "enabled")
SELECT "extension_id", 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_JPEGXL_TITLE', 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_JPEGXL_DESCRIPTION', '', 'com_admin', 1, 'message', '', '', 'admin://components/com_admin/postinstall/htaccessjpegxl.php', 'admin_postinstall_htaccessjpegxl_condition', '6.3.0', 1 FROM "#__extensions" WHERE "name" = 'files_joomla';
