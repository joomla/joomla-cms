CREATE TABLE "#__action_logs_users" (
  "user_id" integer NOT NULL,
  "notify" integer NOT NULL,
  "extensions" text NOT NULL,
  PRIMARY KEY ("user_id")
);

CREATE INDEX "#__action_logs_users_idx_notify" ON "#__action_logs_users" ("notify");
