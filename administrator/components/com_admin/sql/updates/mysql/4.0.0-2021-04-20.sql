UPDATE `#__extensions` SET `name` = 'plg_fields_subform', `element` = 'subform' WHERE `type` = 'plugin' AND `element` = 'subfields' AND `folder` = 'fields' AND `client_id` = 0;
UPDATE `#__fields` SET `type` = 'subform' WHERE `type` = 'subfields';
