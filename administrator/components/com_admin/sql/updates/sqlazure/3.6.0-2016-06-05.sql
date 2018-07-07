--
-- Add ACL check for to #__languages
--

ALTER TABLE [#__languages] ADD [asset_id] [bigint] NOT NULL DEFAULT 0;