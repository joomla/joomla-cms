INSERT INTO `#__guidedtours` (`title`, `description`, `extensions`, `url`, `published`, `language`, `note`, `access`, `uid`, `autostart`, `created`, `created_by`, `modified`, `modified_by`) VALUES
('COM_GUIDEDTOURS_TOUR_WHATSNEW_5_2_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_5_2_DESCRIPTION', '["com_cpanel"]', 'administrator/index.php', 1, '*', '', 1, 'joomla-whatsnew-5-2', 1, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0);
INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_WHATSNEW_5_2_STEP_0_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_5_2_STEP_0_DESCRIPTION', 'right', '#sidebarmenu nav > ul:first-of-type > li:last-child', 0, 1, '', 1, '*', '', '{"required":1,"requiredvalue":""}', MAX(`id`), CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-whatsnew-5-2';
