INSERT INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`)
VALUES
(700, 'COM_CPANEL_MSG_ADDNOSNIFF_TITLE', 'COM_CPANEL_MSG_ADDNOSNIFF_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/addnosniff.php', 'admin_postinstall_addnosniff_condition', '3.9.3', 1);
