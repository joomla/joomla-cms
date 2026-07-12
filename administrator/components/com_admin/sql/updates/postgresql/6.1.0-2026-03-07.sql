UPDATE "#__extensions"
   SET "params" = jsonb_set("params"::jsonb, '{html_height}' , '"550px"')
 WHERE "type" = 'plugin'
   AND "folder" = 'editors'
   AND "element"  = 'tinymce'
   AND "params" <> ''
   AND "params"::jsonb->>'html_height' = '550';

UPDATE "#__extensions"
   SET "params" = jsonb_set("params"::jsonb, '{html_width}' , '"100%"')
 WHERE "type" = 'plugin'
   AND "folder" = 'editors'
   AND "element"  = 'tinymce'
   AND "params" <> ''
   AND "params"::jsonb->>'html_width' = '750';
