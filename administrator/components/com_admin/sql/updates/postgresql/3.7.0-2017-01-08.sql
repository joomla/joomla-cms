-- Normalize ucm_content_table default values.
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_title" SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_body" SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_params" SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metadata" SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_images" SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_urls" SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metakey" SET DEFAULT '';
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metadesc" SET DEFAULT '';
