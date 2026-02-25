-- disable autostart for the previous tour
UPDATE `#__guidedtours` SET `autostart` = 0 WHERE `uid` = 'joomla-whatsnew-6-0';

INSERT INTO `#__guidedtours` (`title`, `description`, `extensions`, `url`, `published`, `language`, `note`, `access`, `uid`, `autostart`, `created`, `created_by`, `modified`, `modified_by`) VALUES
    ('COM_GUIDEDTOURS_TOUR_WHATSNEW_6_1_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_1_DESCRIPTION', '["com_cpanel"]', 'administrator/index.php', 1, '*', '', 1, 'joomla-whatsnew-6-1', 1, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0);

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_1_STEP_0_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_1_STEP_0_DESCRIPTION', 'right', '#sidebarmenu nav > ul:first-of-type > li:last-child', 0, 1, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'joomla-whatsnew-6-1';
