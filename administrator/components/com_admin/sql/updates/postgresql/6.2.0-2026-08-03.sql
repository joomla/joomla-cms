--
-- Insert the entry of workflow_category plugin to extensions
--

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'plg_workflow_category', 'plugin', 'category', 'workflow', 0, 1, 1, 0, 1, '', '{}', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" e WHERE e."type" = 'plugin' AND e."element" = 'category' AND e."folder" = 'workflow' AND e."client_id" = 0);
