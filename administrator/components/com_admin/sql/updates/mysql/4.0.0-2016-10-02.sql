DELETE FROM `#__extensions` WHERE `type` = 'template' AND `element` = 'hathor' AND `client_id` = 1;
DELETE FROM `#__template_styles` WHERE `template` = 'hathor' AND `client_id` = 1;
DELETE FROM `#__extensions` WHERE `type` = 'template' AND `element` = 'isis' AND `client_id` = 1;
DELETE FROM `#__template_styles` WHERE `template` = 'isis' AND `client_id` = 1;
ALTER TABLE `#__user_keys` DROP COLUMN `invalid`;

INSERT INTO `#__extensions` (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
('atum', 'template', 'atum', '', 1, 1, 1, 0, '{}', '{}', 0, '1000-01-01 00:00:00', 0, 0),
('cassiopeia', 'template', 'cassiopeia', '', 0, 1, 1, 0, '{}', '{}', 0, '1000-01-01 00:00:00', 0, 0);

INSERT INTO `#__template_styles` (`template`, `client_id`, `home`, `title`, `params`) VALUES
('atum', 0, '0', 'atum - Default', '{}'),
('cassiopeia', 1, '1', 'cassiopeia - Default', '{}');
