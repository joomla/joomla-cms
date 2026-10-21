-- Remove wrong unique constraint from "#__ucm_content" table
ALTER TABLE "#__ucm_content" DROP CONSTRAINT "#__ucm_content_idx_type_alias_item_id" /** CAN FAIL **/;
