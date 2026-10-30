--
-- Insert back previously unlocked schemaorg and task core plugins if they have been uninstalled
--
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_article', 'plugin', 'article', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'article' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_blogposting', 'plugin', 'blogposting', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'blogposting' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_book', 'plugin', 'book', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'book' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_event', 'plugin', 'event', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'event' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_jobposting', 'plugin', 'jobposting', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'jobposting' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_organization', 'plugin', 'organization', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'organization' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_person', 'plugin', 'person', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'person' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_recipe', 'plugin', 'recipe', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'recipe' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_custom', 'plugin', 'custom', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'custom' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_system_schemaorg', 'plugin', 'schemaorg', 'system', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'schemaorg' AND e.`folder` = 'system' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_task_globalcheckin', 'plugin', 'globalcheckin', 'task', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'globalcheckin' AND e.`folder` = 'task' AND e.`client_id` = 0);

--
-- Update present unlocked schemaorg and task core plugins
--
UPDATE `#__extensions` SET `locked` = 1
 WHERE `type` = 'plugin' AND `folder` = 'schemaorg'
   AND `element` IN ('article', 'blogposting', 'book', 'event', 'jobposting', 'organization', 'person', 'recipe', 'custom')
   AND `locked` <> 1;

UPDATE `#__extensions` SET `locked` = 1
 WHERE `type` = 'plugin' AND `element` = 'schemaorg' AND `folder` = 'system'
   AND `locked` <> 1;

UPDATE `#__extensions` SET `locked` = 1
 WHERE `type` = 'plugin' AND `element` = 'globalcheckin' AND `folder` = 'task'
   AND `locked` <> 1;
