UPDATE "#__extensions"
   SET "params" = REPLACE("params", '"negotiate_tls":1', '"encryption":"tls"')
 WHERE "name" = 'plg_authentication_ldap'
   AND "type" = 'plugin'
   AND "element" = 'ldap'
   AND "folder" = 'authentication'
   AND "client_id" = 0
   AND "params" LIKE '{%"negotiate_tls":1%}';

UPDATE "#__extensions"
   SET "params" = REPLACE("params", '"negotiate_tls":0', '"encryption":"none"')
 WHERE "name" = 'plg_authentication_ldap'
   AND "type" = 'plugin'
   AND "element" = 'ldap'
   AND "folder" = 'authentication'
   AND "client_id" = 0
   AND "params" LIKE '{%"negotiate_tls":0%}';

UPDATE "#__extensions"
   SET "params" = REPLACE("params", '"encryption":"none"', '"encryption":"ssl"')
 WHERE "name" = 'plg_authentication_ldap'
   AND "type" = 'plugin'
   AND "element" = 'ldap'
   AND "folder" = 'authentication'
   AND "client_id" = 0
   AND "params" LIKE '{%"host":"ldaps:\\/\\/%}';

UPDATE "#__extensions"
   SET "params" = REPLACE("params", '"host":"ldaps:\/\/', '"host":"')
 WHERE "name" = 'plg_authentication_ldap'
   AND "type" = 'plugin'
   AND "element" = 'ldap'
   AND "folder" = 'authentication'
   AND "client_id" = 0
   AND "params" LIKE '{%"host":"ldaps:\\/\\/%}';

