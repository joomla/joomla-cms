--
-- Add post-installation message about setting the Content-Encoding header in .htaccess
--
INSERT IGNORE INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`)
SELECT `extension_id`, 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_SETCE_TITLE', 'COM_ADMIN_POSTINSTALL_MSG_HTACCESS_SETCE_DESCRIPTION', '', 'com_admin', 1, 'message', '', '', 'admin://components/com_admin/postinstall/htaccesssetce.php', 'admin_postinstall_htaccesssetce_condition', '4.2.9', 1 FROM `#__extensions` WHERE `name` = 'files_joomla';
