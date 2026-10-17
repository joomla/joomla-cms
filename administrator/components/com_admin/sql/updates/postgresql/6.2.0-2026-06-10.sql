--
-- Insert back previously unlocked schemaorg and task core plugins if they have been uninstalled
--
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_schemaorg_localbusiness', 'plugin', 'localbusiness', 'schemaorg', 0, 0, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'localbusiness' AND e.`folder` = 'schemaorg' AND e.`client_id` = 0);

--
-- Update present unlocked schemaorg and task core plugins
--
UPDATE `#__extensions` SET `locked` = 1
 WHERE `type` = 'plugin' AND `folder` = 'schemaorg'
   AND `element` IN ('article', 'blogposting', 'book', 'event', 'jobposting', 'localbusiness', 'organization', 'person', 'recipe', 'custom')
   AND `locked` <> 1;
