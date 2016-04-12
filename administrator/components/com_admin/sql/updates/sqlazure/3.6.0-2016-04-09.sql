--
-- Add ACL check for to #__menu_types
--

ALTER TABLE [#__menu_types] ADD [asset_id] [bigint] NOT NULL DEFAULT 0;