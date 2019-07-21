DROP INDEX "#__users_email";
CREATE UNIQUE INDEX "#__users_email_lower" ON "#__users" ((lower("email")));
