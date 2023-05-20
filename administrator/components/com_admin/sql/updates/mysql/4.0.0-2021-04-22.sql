-- From 4.0.0-2021-02-28.sql
DELETE FROM `#__postinstall_messages`
 WHERE `title_key`
    IN ('COM_CPANEL_MSG_EACCELERATOR_TITLE',
        'COM_CPANEL_MSG_HTACCESS_TITLE',
        'COM_CPANEL_MSG_JOOMLA40_PRE_CHECKS_TITLE',
        'COM_CPANEL_MSG_UPDATEDEFAULTSETTINGS_TITLE',
        'PLG_PLG_RECAPTCHA_VERSION_1_POSTINSTALL_TITLE',
        'TPL_HATHOR_MESSAGE_POSTINSTALL_TITLE');

-- From 4.0.0-2021-04-11.sql
-- The following statement was modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE `#__fields` ADD COLUMN `only_use_in_subform` tinyint NOT NULL DEFAULT 0 /** CAN FAIL **/;

-- From 4.0.0-2021-04-20.sql
UPDATE `#__extensions` SET `name` = 'plg_fields_subform', `element` = 'subform' WHERE `name` = 'plg_fields_subfields' AND `type` = 'plugin' AND `element` = 'subfields' AND `folder` = 'fields' AND `client_id` = 0;
UPDATE `#__fields` SET `type` = 'subform' WHERE `type` = 'subfields';

-- From 4.0.0-2021-04-22.sql
-- The following statement was modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE `#__mail_templates` ADD COLUMN `extension` varchar(127) NOT NULL DEFAULT '' AFTER `template_id` /** CAN FAIL **/;
UPDATE `#__mail_templates` SET `extension` = SUBSTRING(`template_id`, 1, POSITION('.' IN `template_id`) - 1);
