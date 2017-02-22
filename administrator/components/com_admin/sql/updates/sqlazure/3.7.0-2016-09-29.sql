SET IDENTITY_INSERT [#__postinstall_messages] ON;

INSERT INTO [#__postinstall_messages] ([extension_id], [title_key], [description_key], [action_key], [language_extension], [language_client_id], [type], [action_file], [action], [condition_file], [condition_method], [version_introduced], [enabled])
SELECT 700, 'COM_CPANEL_MSG_JOOMLA40_PRE_CHECKS_TITLE', 'COM_CPANEL_MSG_JOOMLA40_PRE_CHECKS_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/joomla40checks.php', 'admin_postinstall_joomla40checks_condition', '3.7.0', 1;

SET IDENTITY_INSERT [#__postinstall_messages] OFF;