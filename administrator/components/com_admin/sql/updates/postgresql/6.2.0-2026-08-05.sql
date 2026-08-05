--
-- Add show_subcat_desc param to com_content
--

UPDATE "#_extensions"
SET "params" = JSON_SET("params", '$.show_subcat_image', '0')
WHERE "element" = 'com_content'
  AND "type" = 'component';
