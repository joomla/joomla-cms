UPDATE `#__modules`
   SET `params` = REPLACE(`params`,'"layout":"cassiopeia:dropdown-metismenu"','"layout":"cassiopeia:collapse-metismenu"')
 WHERE `client_id` = 0
   AND `module` = 'mod_menu'
   AND `position` = 'menu'
   AND `params` LIKE '{%"layout":"cassiopeia:dropdown-metismenu"%}';
