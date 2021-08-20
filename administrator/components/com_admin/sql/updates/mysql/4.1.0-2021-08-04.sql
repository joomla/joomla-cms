-- Add `com_scheduler` to `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`,
							 `protected`, `locked`, `manifest_cache`, `params`, `custom_data`)
VALUES (0, 'com_scheduler', 'component', 'com_scheduler', '', 1, 1, 1, 0, 1, '', '', '');

-- Add `plg_job_testjob` to `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`,
							 `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
VALUES (0, 'plg_job_testjob', 'plugin', 'testjob', 'job', 0, 1, 1, 0, 0, '', '{}', '', 15, 0);
