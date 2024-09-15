INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_behaviour_compat', 'plugin', 'compat', 'behaviour', 0, 1, 1, 0, 1, '', '{"classes_aliases":"1","es5_assets":"1"}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'compat' AND e.`folder` = 'behaviour' AND e.`client_id` = 0);
