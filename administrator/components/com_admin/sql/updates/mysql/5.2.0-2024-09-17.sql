DELETE FROM `#__guidedtour_steps` WHERE `tour_id` = (SELECT `id` FROM `#__guidedtours` WHERE `uid` = 'joomla-guidedtours');

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_NEW_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_NEW_DESCRIPTION', 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_guidedtours&view=tours', 1, '*', '', `id`, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-guidedtours'

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_TITLE_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_TITLE_DESCRIPTION', 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', 1, '*', '', `id`, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-guidedtours';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_URL_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_URL_DESCRIPTION', 'top', '#jform_url', 2, 2, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', 1, '*', '', `id`, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-guidedtours';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONTENT_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONTENT_DESCRIPTION', 'bottom', '#jform_description,#jform_description_ifr', 2, 3, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', 1, '*', '', `id`, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-guidedtours';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_COMPONENT_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_COMPONENT_DESCRIPTION', 'top', 'joomla-field-fancy-select .choices', 2, 3, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', 1, '*', '', `id`, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-guidedtours';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_AUTOSTART_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_AUTOSTART_DESCRIPTION', 'bottom', '#jform_autostart0', 2, 3, '', 1, '*', '', `id`, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-guidedtours';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_SAVECLOSE_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_SAVECLOSE_DESCRIPTION', 'top', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', 1, '*', '', `id`, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-guidedtours';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `tour_id`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONGRATULATIONS_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONGRATULATIONS_DESCRIPTION', 'bottom', '', 0, 1, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', 1, '*', '', `id`, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
  FROM `#__guidedtours`
 WHERE `uid` = 'joomla-guidedtours';
