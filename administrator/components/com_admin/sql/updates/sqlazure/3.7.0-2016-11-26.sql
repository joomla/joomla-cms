-- Normalize asset_id fields.
ALTER TABLE [#__ucm_content] ALTER COLUMN [asset_id] [bigint] NOT NULL DEFAULT 0;
