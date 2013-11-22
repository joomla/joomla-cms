ALTER TABLE "#__users" ADD COLUMN "requireReset" smallint DEFAULT 0;
COMMENT ON COLUMN "#__users"."requireReset" IS 'Require user to reset password on next login';
