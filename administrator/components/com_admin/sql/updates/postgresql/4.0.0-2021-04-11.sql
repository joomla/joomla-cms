-- Add locked field to extensions table.
ALTER TABLE "#__fields" ADD COLUMN "use_in_subform" smallint DEFAULT 0 NOT NULL;