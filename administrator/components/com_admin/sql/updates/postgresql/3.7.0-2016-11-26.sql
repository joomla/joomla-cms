-- Normalize asset_id fields.
COMMENT ON COLUMN "#__menu_types"."asset_id" IS 'FK to the #__assets table.';
COMMENT ON COLUMN "#__languages"."asset_id" IS 'FK to the #__assets table.';
