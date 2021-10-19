-- From 4.0.0-2020-04-11.sql
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(0, 'plg_quickicon_downloadkey', 'plugin', 'downloadkey', 'quickicon', 0, 1, 1, 0, 1, '', '', '', 0, NULL, 0, 0);

-- From 4.0.0-2020-04-16.sql
INSERT INTO `#__mail_templates` (`template_id`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
('com_contact.mail', '', 'COM_CONTACT_ENQUIRY_SUBJECT', 'COM_CONTACT_ENQUIRY_TEXT', '', '', '{"tags":["sitename","name","email","subject","body","url","customfields"]}'),
('com_contact.mail.copy', '', 'COM_CONTACT_COPYSUBJECT_OF', 'COM_CONTACT_COPYTEXT_OF', '', '', '{"tags":["sitename","name","email","subject","body","url","customfields"]}'),
('com_users.massmail.mail', '', 'COM_USERS_MASSMAIL_MAIL_SUBJECT', 'COM_USERS_MASSMAIL_MAIL_BODY', '', '', '{"tags":["subject","body","subjectprefix","bodysuffix"]}'),
('com_users.password_reset', '', 'COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT', 'COM_USERS_EMAIL_PASSWORD_RESET_BODY', '', '', '{"tags":["name","email","sitename","link_text","link_html","token"]}'),
('com_users.reminder', '', 'COM_USERS_EMAIL_USERNAME_REMINDER_SUBJECT', 'COM_USERS_EMAIL_USERNAME_REMINDER_BODY', '', '', '{"tags":["name","username","sitename","email","link_text","link_html"]}'),
('plg_system_updatenotification.mail', '', 'PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_SUBJECT', 'PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_BODY', '', '', '{"tags":["newversion","curversion","sitename","url","link","releasenews"]}'),
('plg_user_joomla.mail', '', 'PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT', 'PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY', '', '', '{"tags":["name","sitename","url","username","password","email"]}');

-- From 4.0.0-2020-05-21.sql
-- Renaming table
RENAME TABLE `#__ucm_history` TO `#__history`;
-- Rename ucm_item_id to item_id as the new primary identifier for the original content item
ALTER TABLE `#__history` CHANGE `ucm_item_id` `item_id` VARCHAR(50) NOT NULL AFTER `version_id`;
-- Extend the original field content with the alias of the content type
UPDATE #__history AS h INNER JOIN #__content_types AS c ON h.ucm_type_id = c.type_id SET h.item_id = CONCAT(c.type_alias, '.', h.item_id);
-- Now we don't need the ucm_type_id anymore and drop it.
ALTER TABLE `#__history` DROP COLUMN `ucm_type_id`;
ALTER TABLE `#__history` MODIFY `save_date` datetime NOT NULL;

-- From 4.0.0-2020-05-29.sql
ALTER TABLE `#__extensions` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__menu` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__modules` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__tags` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__update_sites` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__user_notes` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__workflows` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__workflow_stages` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__workflow_transitions` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__banners` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__banner_clients` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__contact_details` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__content` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__finder_filters` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__newsfeeds` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__categories` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__fields` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__fields_groups` MODIFY `checked_out` INT UNSIGNED;
ALTER TABLE `#__ucm_content` MODIFY `core_checked_out_user_id` INT UNSIGNED;

UPDATE `#__extensions` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__menu` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__modules` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__tags` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__update_sites` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__user_notes` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__workflows` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__workflow_stages` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__workflow_transitions` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__banners` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__banner_clients` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__contact_details` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__content` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__finder_filters` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__newsfeeds` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__categories` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__fields` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__fields_groups` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__ucm_content` SET `core_checked_out_user_id` = null WHERE `core_checked_out_user_id` = 0;
