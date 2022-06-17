-- Add `com_guidedtours` to `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`,
							 `protected`, `locked`, `manifest_cache`, `params`, `custom_data`)
VALUES (0, 'com_guidedtours', 'component', 'com_guidedtours', '', 1, 1, 1, 0, 1, '', '', '');

-- Add `plg_system_tour` to `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`,
							 `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
VALUES (0, 'plg_system_tour', 'plugin', 'Guided Tours Plugin', 'system', 0, 1, 1, 0, 0, '', '{}', '', 15, 0);