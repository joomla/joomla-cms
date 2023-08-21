UPDATE "#__extensions"
   SET "params" = jsonb_set("params"::jsonb, '{filter}' , '"\\Joomla\\CMS\\Component\\ComponentHelper::filterText"')
 WHERE "type" = 'plugin'
   AND "folder" = 'fields'
   AND "params" <> ''
   AND "params"::jsonb->>'filter' = 'JComponentHelper::filterText';
