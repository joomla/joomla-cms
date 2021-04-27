UPDATE `#__modules`
   SET `params` = JSON_SET(`params`, '$.bootstrap_size', '12')
 WHERE `client_id` = 1
   AND `module`
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
   AND `position`
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
   AND `params` LIKE '{%}'
   AND (JSON_EXTRACT(`params`, '$.bootstrap_size') IS NULL OR JSON_EXTRACT(`params`, '$.bootstrap_size') != '12');

UPDATE `#__modules`
   SET `params` = JSON_SET(`params`, '$.header_tag', 'h2')
 WHERE `client_id` = 1
   AND `module`
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
   AND `position`
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
   AND `params` LIKE '{%}'
   AND (JSON_EXTRACT(`params`, '$.header_tag') IS NULL OR JSON_EXTRACT(`params`, '$.header_tag') != 'h2');
