ALTER TABLE "#__update_sites" ADD COLUMN "checked_out" bigint DEFAULT 0 NOT NULL;
ALTER TABLE "#__update_sites" ADD COLUMN "checked_out_time" timestamp without time zone DEFAULT NULL;
