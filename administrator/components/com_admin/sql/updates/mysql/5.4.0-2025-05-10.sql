INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_quickicon_autoupdate', 'plugin', 'autoupdate', 'quickicon', 0, 1, 1, 0, 1, '', '', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'autoupdate' AND e.`folder` = 'quickicon' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_webservices_joomlaupdate', 'plugin', 'joomlaupdate', 'webservices', 0, 1, 1, 0, 1, '', '', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'joomlaupdate' AND e.`folder` = 'webservices' AND e.`client_id` = 0);

INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
('com_joomlaupdate.update.success', 'com_joomlaupdate', '', 'COM_JOOMLAUPDATE_UPDATE_SUCCESS_MAIL_SUBJECT', 'COM_JOOMLAUPDATE_UPDATE_SUCCESS_MAIL_BODY', '', '', '{"tags":["newversion","oldversion","sitename","url"]}'),
('com_joomlaupdate.update.failed', 'com_joomlaupdate', '', 'COM_JOOMLAUPDATE_UPDATE_FAILED_MAIL_SUBJECT', 'COM_JOOMLAUPDATE_UPDATE_FAILED_MAIL_BODY', '', '', '{"tags":["newversion","oldversion","sitename","url"]}');

-- add post-installation message for automated updates
INSERT INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`)
SELECT `extension_id`, 'COM_JOOMLAUPDATE_POSTINSTALL_MSG_AUTOMATED_UPDATES_TITLE', 'COM_JOOMLAUPDATE_POSTINSTALL_MSG_AUTOMATED_UPDATES_DESCRIPTION', 'COM_JOOMLAUPDATE_POSTINSTALL_MSG_AUTOMATED_UPDATES_ACTION', 'com_joomlaupdate', 1, 'action', 'admin://components/com_joomlaupdate/postinstall/autoupdate.php', 'com_joomlaupdate_postinstall_autoupdate_action', 'admin://components/com_joomlaupdate/postinstall/autoupdate.php', 'com_joomlaupdate_postinstall_autoupdate_condition', '5.4.0', 1 FROM `#__extensions` WHERE `name` = 'files_joomla';

-- disable autostart for the previous tour
UPDATE `#__guidedtours` SET `autostart` = 0 WHERE `uid` = 'joomla-whatsnew-5-3';

INSERT INTO `#__guidedtours` (`title`, `description`, `extensions`, `url`, `published`, `language`, `note`, `access`, `uid`, `autostart`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_WHATSNEW_5_4_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_5_4_DESCRIPTION', '["com_cpanel"]', 'administrator/index.php', 1, '*', '', 1, 'joomla-whatsnew-5-4', 1, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
WHERE NOT EXISTS (SELECT * FROM `#__guidedtours` g WHERE g.`uid` = 'joomla-whatsnew-5-4');

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_WHATSNEW_5_4_STEP_0_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_5_4_STEP_0_DESCRIPTION', 'right', '#sidebarmenu nav > ul:first-of-type > li:last-child', 0, 1, '', 1, '*', '', '"{\"required\":1,\"requiredvalue\":\"\"}"', MAX(`id`), CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
FROM `#__guidedtours`
WHERE `uid` = 'joomla-whatsnew-5-4';
