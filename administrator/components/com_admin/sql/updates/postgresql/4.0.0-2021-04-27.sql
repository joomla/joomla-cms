UPDATE "#__modules"
   SET "params" = jsonb_set("params"::jsonb, '{bootstrap_size}', '"12"', true)
 WHERE "client_id" = 1
   AND "module"
    IN (
       'mod_latest',
       'mod_latestactions',
       'mod_logged',
       'mod_popular',
       'mod_privacy_dashboard',
       'mod_privacy_status'
       'mod_quickicon',
       'mod_sampledata',
       'mod_submenu'
       )
   AND "position"
    IN (
       'cpanel',
       'cpanel-components',
       'cpanel-content',
       'cpanel-help',
       'cpanel-menus',
       'cpanel-privacy',
       'cpanel-system',
       'cpanel-users',
       'icon'
       )
   AND "params" LIKE '{%}'
   AND ("params"::json->>'bootstrap_size' IS NULL OR "params"::json->>'bootstrap_size' <> '12');

UPDATE "#__modules"
   SET "params" = jsonb_set("params"::jsonb, '{header_tag}', '"h2"', true)
 WHERE "client_id" = 1
   AND "module"
    IN (
       'mod_latest',
       'mod_latestactions',
       'mod_logged',
       'mod_popular',
       'mod_privacy_dashboard',
       'mod_privacy_status'
       'mod_quickicon',
       'mod_sampledata',
       'mod_submenu'
       )
   AND "position"
    IN (
       'cpanel',
       'cpanel-components',
       'cpanel-content',
       'cpanel-help',
       'cpanel-menus',
       'cpanel-privacy',
       'cpanel-system',
       'cpanel-users',
       'icon'
       )
   AND "params" LIKE '{%}'
   AND ("params"::json->>'header_tag' IS NULL OR "params"::json->>'header_tag' <> 'h2');
