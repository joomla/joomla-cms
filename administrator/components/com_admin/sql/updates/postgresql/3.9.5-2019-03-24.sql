DROP INDEX "#__users_email";
CREATE UNIQUE INDEX "#__users_email" ON "#__users" ((lower("email")));
