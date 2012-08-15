ALTER TABLE "#__redirect_links" ADD COLUMN "hits" integer NOT NULL DEFAULT 0;
ALTER TABLE "#__users" ADD COLUMN "lastResetTime" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL;
ALTER TABLE "#__users" ADD COLUMN "resetCount" integer NOT NULL DEFAULT 0;

COMMENT ON COLUMN "#__users"."lastResetTime" IS 'Date of last password reset';
COMMENT ON COLUMN "#__users"."resetCount" IS 'Count of password resets since lastResetTime';