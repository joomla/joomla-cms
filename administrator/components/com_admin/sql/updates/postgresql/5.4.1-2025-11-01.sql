UPDATE "#__extensions"
SET "params" = jsonb_set("params"::jsonb, '{removed_asset}', '"1"', true)
WHERE "type" = 'plugin' AND "element" = 'compat' AND "folder" = 'behaviour'
AND "params"::jsonb->>'removed_asset' IS NULL;
