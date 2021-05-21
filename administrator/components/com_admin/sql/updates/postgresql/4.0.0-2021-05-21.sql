UPDATE "#__modules"
   SET "params" = REPLACE("params",'"layout":"cassiopeia:dropdown-metismenu"','"layout":"cassiopeia:collapse-metismenu"')
 WHERE "client_id" = 0
   AND "module" = 'mod_menu'
   AND "position" = 'menu'
   AND "params" LIKE '{%"layout":"cassiopeia:dropdown-metismenu"%}';

UPDATE "#__modules"
   SET "params" = REPLACE("params",'"layout":"_:default"','"layout":"_:collapse-default"')
 WHERE "client_id" = 0
   AND "module" = 'mod_menu'
   AND "position" = 'menu'
   AND "params" LIKE '{%"layout":"_:default"%}';

UPDATE "#__modules"
   SET "params" = REPLACE("params",'"layout":""','"layout":"_:collapse-default"')
 WHERE "client_id" = 0
   AND "module" = 'mod_menu'
   AND "position" = 'menu'
   AND "params" LIKE '{%"layout":""%}';
