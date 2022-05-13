CREATE TABLE IF NOT EXISTS "#__user_tfa" (
  "id" serial NOT NULL,
  "user_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "method" varchar(100) DEFAULT '' NOT NULL,
  "default" smallint DEFAULT 0,
  "options" text NOT NULL,
  "created_on" timestamp without time zone NOT NULL,
  "last_used" timestamp without time zone NOT NULL,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__user_tfa_idx_user_id" ON "#__user_tfa" ("user_id");

COMMENT ON TABLE "#__user_tfa" IS 'Two Factor Authentication settings';
