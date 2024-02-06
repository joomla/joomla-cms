-- From 4.0.0-2020-12-08.sql
-- The following statement was modified for 4.1.1 by adding the "IGNORE" keyword.
-- See https://github.com/joomla/joomla-cms/pull/37156
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
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
('com_users.registration.user.registration_mail', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_BODY_NOPW', '', '', '{"tags":["name","sitename","siteurl","username"]}'),
('com_users.registration.user.registration_mail_w_pw', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_BODY', '', '', '{"tags":["name","sitename","siteurl","username","password_clear"]}'),
('com_users.registration.admin.new_notification', '', 'COM_USERS_EMAIL_ACCOUNT_DETAILS', 'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY', '', '', '{"tags":["name","sitename","siteurl","username"]}'),
('com_users.registration.user.admin_activated', '', 'COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT', 'COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY', '', '', '{"tags":["name","sitename","siteurl","username"]}'),
('com_users.registration.admin.verification_request', '', 'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT', 'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY', '', '', '{"tags":["name","sitename","email","username","activate"]}'),
('plg_system_privacyconsent.request.reminder', '', 'PLG_SYSTEM_PRIVACYCONSENT_EMAIL_REMIND_SUBJECT', 'PLG_SYSTEM_PRIVACYCONSENT_EMAIL_REMIND_BODY', '', '', '{"tags":["sitename","url","tokenurl","formurl","token"]}'),
('com_messages.new_message', '', 'COM_MESSAGES_NEW_MESSAGE', 'COM_MESSAGES_NEW_MESSAGE_BODY', '', '', '{"tags":["subject","message","fromname","sitename","siteurl","fromemail","toname","toemail"]}')
;

-- From 4.0.0-2020-12-19.sql
UPDATE `#__banners` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__categories` SET `created_time` = '1980-01-01 00:00:00' WHERE `created_time` = '0000-00-00 00:00:00';
UPDATE `#__categories` SET `modified_time` = `created_time` WHERE `modified_time` = '0000-00-00 00:00:00';

UPDATE `#__contact_details` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__contact_details` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__content` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__newsfeeds` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__newsfeeds` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_created_time` = '1980-01-01 00:00:00'
 WHERE `core_type_alias`
    IN ('com_banners.banner'
       ,'com_banners.category'
       ,'com_contact.category'
       ,'com_contact.contact'
       ,'com_content.article'
       ,'com_content.category'
       ,'com_newsfeeds.category'
       ,'com_newsfeeds.newsfeed'
       ,'com_users.category')
   AND `core_created_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_modified_time` = `core_created_time`
 WHERE `core_type_alias`
    IN ('com_banners.banner'
       ,'com_banners.category'
       ,'com_contact.category'
       ,'com_contact.contact'
       ,'com_content.article'
       ,'com_content.category'
       ,'com_newsfeeds.category'
       ,'com_newsfeeds.newsfeed'
       ,'com_users.category')
   AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__redirect_links` SET `created_date` = '1980-01-01 00:00:00' WHERE `created_date` = '0000-00-00 00:00:00';
UPDATE `#__redirect_links` SET `modified_date` = `created_date` WHERE `modified_date` = '0000-00-00 00:00:00';

UPDATE `#__users` SET `registerDate` = '1980-01-01 00:00:00' WHERE `registerDate` = '0000-00-00 00:00:00';

-- From 4.0.0-2020-12-20.sql
UPDATE `#__modules` SET `title` = 'Notifications' WHERE `#__modules`.`id` = 9;
