INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_fields_number', 'plugin', 'number', 'fields', 0, 1, 1, 0, 1, '', '{"min":"1.0","max":"100.0","step":"0.1","currency":"0","position":"0","decimals":"2"}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'number' AND e.`folder` = 'fields' AND e.`client_id` = 0);
