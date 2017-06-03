--
-- Add ACL check for to #__languages
--

ALTER TABLE "#__languages" ADD COLUMN "asset_id" bigint DEFAULT 0 NOT NULL;