CREATE TABLE "#__action_logs_users" (
  "user_id" serial NOT NULL,
  "notify" serial NOT NULL,
  CONSTRAINT "#__action_logs_user_idx_user_id" UNIQUE ("user_id")
);

CREATE INDEX "#__action_logs_users_idx_notify" ON "#__action_logs_users" ("notify");
