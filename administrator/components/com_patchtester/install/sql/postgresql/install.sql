CREATE TABLE "#__patchtester_tests" (
  "id" serial NOT NULL,
  "pull_id" bigint NOT NULL,
  "data" text NOT NULL,
  "patched_by" bigint NOT NULL,
  "applied" bigint NOT NULL,
  "applied_version" character varying(25) NOT NULL,
  "rating" bigint NOT NULL,
  "comments" character varying(3000) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);
