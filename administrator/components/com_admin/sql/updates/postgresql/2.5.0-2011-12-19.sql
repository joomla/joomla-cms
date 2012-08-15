CREATE TABLE "#__user_notes" (
  "id" serial NOT NULL,
  "user_id" integer DEFAULT 0 NOT NULL,
  "catid" integer DEFAULT 0 NOT NULL,
  "subject" character varying(100) DEFAULT '' NOT NULL,
  "body" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_user_id" integer DEFAULT 0 NOT NULL,
  "created_time" timestamp DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_user_id" integer NOT NULL,
  "modified_time" timestamp DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "review_time" timestamp DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_up" timestamp DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp DEFAULT '1970-01-01 00:00:00' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__user_notes_idx_user_id" on "#__user_notes" ("user_id");
CREATE INDEX "#__user_notes_idx_category_id" on "#__user_notes" ("catid");