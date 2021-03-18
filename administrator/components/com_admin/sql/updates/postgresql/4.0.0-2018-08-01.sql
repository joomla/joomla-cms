ALTER TABLE "#__ucm_content" ALTER COLUMN "core_created_time" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_modified_time" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_publish_up" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_publish_up" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_publish_down" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_publish_down" DROP DEFAULT;

ALTER TABLE "#__ucm_content" ALTER COLUMN "core_checked_out_time" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_checked_out_time" DROP DEFAULT;
