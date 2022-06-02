UPDATE `#__extensions`
   SET `params` = REPLACE(`params`,'"negotiate_tls":1','"encryption":"tls"')
 WHERE `client_id` = 0
   AND `name` = 'plg_authentication_ldap'
   AND `params` LIKE '{%"negotiate_tls":1%}';

UPDATE `#__extensions`
   SET `params` = REPLACE(`params`,'"negotiate_tls":0','"encryption":"none"')
 WHERE `client_id` = 0
   AND `name` = 'plg_authentication_ldap'
   AND `params` LIKE '{%"negotiate_tls":0%}';
