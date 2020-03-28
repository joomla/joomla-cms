DROP INDEX "#__users_username";
ALTER TABLE "#__users" ADD CONSTRAINT "#__users_idx_username" UNIQUE ("username");
