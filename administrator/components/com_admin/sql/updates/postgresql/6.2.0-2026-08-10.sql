--
-- Add show_subcat_image param to com_contact
--

UPDATE "#__extensions"
SET "params" = jsonb_set("params"::jsonb, '{show_subcat_image}', '0', true)
WHERE "element" = 'com_contact'
  AND "type" = 'component'
  AND "params"::jsonb->>'show_subcat_image' IS NULL;
