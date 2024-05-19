--
-- Add post-installation message about setting the Content-Encoding header in .htaccess
--
INSERT IGNORE INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`)
SELECT `extension_id`, 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_BROTLI_TITLE', 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_BROTLI_DESCRIPTION', '', 'com_admin', 1, 'message', '', '', 'admin://components/com_admin/postinstall/htaccessbrotli.php', 'admin_postinstall_htaccessbrotli_condition', '4.4.4', 1 FROM `#__extensions` WHERE `name` = 'files_joomla';
