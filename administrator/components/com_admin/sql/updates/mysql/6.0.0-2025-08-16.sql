INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_fields_note', 'plugin', 'note', 'fields', 0, 1, 1, 0, 1, '', '{"class":"alert alert-info","heading":"h4"}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'note' AND e.`folder` = 'fields' AND e.`client_id` = 0);
