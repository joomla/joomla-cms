--
-- Add show_subcat_image param to com_newsfeeds
--

UPDATE "#__extensions"
SET "params" = jsonb_set("params"::jsonb, '{show_subcat_image}', '0', true)
WHERE "element" = 'com_newsfeeds'
  AND "type" = 'component'
  AND "params"::jsonb->>'show_subcat_image' IS NULL;
