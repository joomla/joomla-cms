UPDATE `#__modules`
   SET `params` = JSON_REPLACE(`params`, '$.documentation_url', 'https://guide.joomla.org/')
 WHERE `module` = 'mod_loginsupport'
   AND `client_id` = 1
   AND `params` <> ''
   AND JSON_UNQUOTE(JSON_EXTRACT(`params`, '$.documentation_url')) = 'https://docs.joomla.org/';