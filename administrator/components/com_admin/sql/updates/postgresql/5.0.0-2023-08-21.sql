UPDATE "#__extensions"
   SET "params" = jsonb_set("params"::jsonb, '{filter}' , '"\\\\Joomla\\\\CMS\\\\Component\\\\ComponentHelper::filterText"')
 WHERE "type" = 'plugin'
   AND "folder" = 'fields'
   AND "element" IN ('editor', 'text', 'textarea')
   AND "params" <> ''
   AND "params"::jsonb->>'filter' = 'JComponentHelper::filterText';

UPDATE "#__fields"
   SET "fieldparams" = jsonb_set("fieldparams"::jsonb, '{filter}' , '"\\\\Joomla\\\\CMS\\\\Component\\\\ComponentHelper::filterText"')
 WHERE "type" IN ('editor', 'text', 'textarea')
   AND "fieldparams" <> ''
   AND "fieldparams"::jsonb->>'filter' = 'JComponentHelper::filterText';
