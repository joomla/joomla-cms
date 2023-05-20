-- From 4.0.0-2021-05-01.sql
UPDATE `#__template_styles`
   SET `params` = '{"hue":"hsl(214, 63%, 20%)","bg-light":"#f0f4fb","text-dark":"#495057","text-light":"#ffffff","link-color":"#2a69b8","special-color":"#001b4c","monochrome":"0","loginLogo":"","loginLogoAlt":"","logoBrandLarge":"","logoBrandLargeAlt":"","logoBrandSmall":"","logoBrandSmallAlt":""}'
 WHERE `template` = 'atum'
   AND `client_id` = 1;

-- From 4.0.0-2021-05-04.sql
DELETE FROM `#__extensions` WHERE `name` = 'com_csp' and `type` = 'component' and `element` = 'com_csp';
DROP TABLE IF EXISTS `#__csp`;

-- From 4.0.0-2021-05-07.sql
UPDATE `#__mail_templates`
   SET `subject` = 'COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_SUBJECT',
       `body` = 'COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_BODY'
 WHERE `template_id` = 'com_privacy.userdataexport';

-- From 4.0.0-2021-05-10.sql
-- The following statement was modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE `#__finder_taxonomy` ADD INDEX `idx_level` (`level`) /** CAN FAIL **/;

-- From 4.0.0-2021-05-21.sql
UPDATE `#__modules`
   SET `params` = REPLACE(`params`,'"layout":"cassiopeia:dropdown-metismenu"','"layout":"cassiopeia:collapse-metismenu"')
 WHERE `client_id` = 0
   AND `module` = 'mod_menu'
   AND `position` = 'menu'
   AND `params` LIKE '{%"layout":"cassiopeia:dropdown-metismenu"%}';

-- From 4.0.0-2021-05-30.sql
UPDATE `#__update_sites`
   SET `location` = 'https://update.joomla.org/language/translationlist_4.xml'
 WHERE `location` = 'https://update.joomla.org/language/translationlist_3.xml';
