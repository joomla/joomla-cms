DELETE FROM `#__extensions` WHERE `element` = 'hathor';
DELETE FROM `#__template_styles` WHERE `template` = 'hathor';
ALTER TABLE `#__user_keys` DROP COLUMN `invalid`;
