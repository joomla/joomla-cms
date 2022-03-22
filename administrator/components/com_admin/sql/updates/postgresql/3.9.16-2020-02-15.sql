ALTER TABLE "#__categories" ALTER COLUMN "description" DROP NOT NULL;
ALTER TABLE "#__categories" ALTER COLUMN "description" DROP DEFAULT;

ALTER TABLE "#__categories" ALTER COLUMN "params" DROP NOT NULL;
ALTER TABLE "#__categories" ALTER COLUMN "params" DROP DEFAULT;

ALTER TABLE "#__fields" ALTER COLUMN "default_value" DROP NOT NULL;
ALTER TABLE "#__fields" ALTER COLUMN "default_value" DROP DEFAULT;

ALTER TABLE "#__fields" ALTER COLUMN "description" DROP DEFAULT;

ALTER TABLE "#__fields" ALTER COLUMN "params" DROP DEFAULT;

ALTER TABLE "#__fields" ALTER COLUMN "fieldparams" DROP DEFAULT;

ALTER TABLE "#__fields_groups" ALTER COLUMN "params" DROP DEFAULT;

ALTER TABLE "#__fields_values" ALTER COLUMN "value" DROP NOT NULL;
ALTER TABLE "#__fields_values" ALTER COLUMN "value" DROP DEFAULT;

ALTER TABLE "#__finder_links" ALTER COLUMN "description" DROP NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "description" DROP DEFAULT;

ALTER TABLE "#__menu" ALTER COLUMN "params" DROP DEFAULT;

ALTER TABLE "#__modules" ALTER COLUMN "content" DROP NOT NULL;
ALTER TABLE "#__modules" ALTER COLUMN "content" DROP DEFAULT;

ALTER TABLE "#__tags" ALTER COLUMN "description" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_body" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_body" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_params" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_params" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metadata" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metadata" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_images" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_images" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_urls" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_urls" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metakey" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metakey" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metadesc" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_metadesc" DROP DEFAULT;

ALTER TABLE "#__action_logs" ALTER COLUMN "message" DROP DEFAULT;
