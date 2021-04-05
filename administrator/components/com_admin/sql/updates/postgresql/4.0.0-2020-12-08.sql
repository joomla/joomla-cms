INSERT INTO "#__mail_templates" ("template_id", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES
('com_actionlogs.notification', '', 'COM_ACTIONLOGS_EMAIL_SUBJECT', 'COM_ACTIONLOGS_EMAIL_BODY', 'COM_ACTIONLOGS_EMAIL_HTMLBODY', '', '{"tags":["message","date","extension"]}'),
('com_privacy.userdataexport', '', 'COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_BODY', 'COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_SUBJECT', '', '', '{"tags":["sitename","url"]}'),
('com_privacy.notification.export', '', 'COM_PRIVACY_EMAIL_REQUEST_SUBJECT_EXPORT_REQUEST', 'COM_PRIVACY_EMAIL_REQUEST_BODY_EXPORT_REQUEST', '', '', '{"tags":["sitename","url","tokenurl","formurl","token"]}'),
('com_privacy.notification.remove', '', 'COM_PRIVACY_EMAIL_REQUEST_SUBJECT_REMOVE_REQUEST', 'COM_PRIVACY_EMAIL_REQUEST_BODY_REMOVE_REQUEST', '', '', '{"tags":["sitename","url","tokenurl","formurl","token"]}'),
('com_privacy.notification.admin.export', '', 'COM_PRIVACY_EMAIL_ADMIN_REQUEST_SUBJECT_EXPORT_REQUEST', 'COM_PRIVACY_EMAIL_ADMIN_REQUEST_BODY_EXPORT_REQUEST', '', '', '{"tags":["sitename","url","tokenurl","formurl","token"]}'),
('com_privacy.notification.admin.remove', '', 'COM_PRIVACY_EMAIL_ADMIN_REQUEST_SUBJECT_REMOVE_REQUEST', 'COM_PRIVACY_EMAIL_ADMIN_REQUEST_BODY_REMOVE_REQUEST', '', '', '{"tags":["sitename","url","tokenurl","formurl","token"]}'),
('com_users.registration.user.admin_activation', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW', '', '', '{"tags":["name","sitename","activate","siteurl","username"]}'),
('com_users.registration.user.admin_activation_w_pw', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY', '', '', '{"tags":["name","sitename","activate","siteurl","username","password_clear"]}'),
('com_users.registration.user.self_activation', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW', '', '', '{"tags":["name","sitename","activate","siteurl","username"]}'),
('com_users.registration.user.self_activation_w_pw', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY', '', '', '{"tags":["name","sitename","activate","siteurl","username","password_clear"]}'),
('com_users.registration.user.registration_mail', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_BODY_NOPW', '', '', '{"tags":["name","sitename","activate","siteurl","username"]}'),
('com_users.registration.user.registration_mail_w_pw', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_BODY', '', '', '{"tags":["name","sitename","activate","siteurl","username","password_clear"]}'),
('com_users.registration.admin.new_notification', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY', '', '', '{"tags":["name","sitename","siteurl","username"]}'),
('com_users.registration.user.admin_activated', '', 'COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT', 'COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY', '', '', '{"tags":["name","sitename","siteurl","username"]}'),
('com_users.registration.admin.verification_request', '', 'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT', 'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY', '', '', '{"tags":["name","sitename","email","username","activate"]}'),
('plg_system_privacyconsent.request.reminder', '', 'PLG_SYSTEM_PRIVACYCONSENT_EMAIL_REMIND_SUBJECT', 'PLG_SYSTEM_PRIVACYCONSENT_EMAIL_REMIND_BODY', '', '', '{"tags":["sitename","url","tokenurl","formurl","token"]}'),
('com_messages.new_message', '', 'COM_MESSAGES_NEW_MESSAGE', 'COM_MESSAGES_NEW_MESSAGE_BODY', '', '', '{"tags":["subject","message","fromname","sitename","siteurl","fromemail","toname","toemail"]}')
;
