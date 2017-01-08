-- Normalize ucm_content_table default values.
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_title" SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_body" text SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_params" text SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metadata" text SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_images" text SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_urls" text SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metakey" text SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metadesc" text SET DEFAULT '';
