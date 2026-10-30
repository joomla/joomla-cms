UPDATE "#__modules"
   SET "params" = jsonb_set("params"::jsonb, '{documentation_url}', '"https://guide.joomla.org/"')
 WHERE "module" = 'mod_loginsupport'
   AND "client_id" = 1
   AND "params" <> ''
   AND "params"::jsonb->>'documentation_url' = 'https://docs.joomla.org/';