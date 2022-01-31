UPDATE "#__modules"
   SET "params" = REPLACE("params",'"bootstrap_size":"6"','"bootstrap_size":"12"')
 WHERE "client_id" = 1
   AND "module"
    IN (
       'mod_latest',
       'mod_latestactions',
       'mod_logged',
       'mod_popular',
       'mod_privacy_dashboard',
       'mod_privacy_status',
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
   AND "params" LIKE '{%"bootstrap_size":"6"%}';

UPDATE "#__modules"
   SET "params" = REPLACE("params",'"bootstrap_size": "6"','"bootstrap_size":"12"')
 WHERE "client_id" = 1
   AND "module"
    IN (
       'mod_latest',
       'mod_latestactions',
       'mod_logged',
       'mod_popular',
       'mod_privacy_dashboard',
       'mod_privacy_status',
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
   AND "params" LIKE '{%"bootstrap_size": "6"%}';

UPDATE "#__modules"
   SET "params" = REPLACE("params",'"header_tag":"h3"','"header_tag":"h2"')
 WHERE "client_id" = 1
   AND "module"
    IN (
       'mod_latest',
       'mod_latestactions',
       'mod_logged',
       'mod_popular',
       'mod_privacy_dashboard',
       'mod_privacy_status',
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
   AND "params" LIKE '{%"header_tag":"h3"%}';
