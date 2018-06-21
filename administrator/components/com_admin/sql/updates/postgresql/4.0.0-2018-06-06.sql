CREATE TABLE IF NOT EXISTS "#__finder_logging" (
  "searchterm" character varying(255) NOT NULL DEFAULT '',
  "md5sum" character varying(32) NOT NULL DEFAULT '',
  "query" bytes NOT NULL,
  "hits" integer NOT NULL DEFAULT 1,
  "results" integer NOT NULL DEFAULT 0,
  CONSTRAINT "#__finder_logging_idx_md5sum" UNIQUE ("md5sum")
);
CREATE INDEX "#__finder_logging_idx_md5sum" on "#__finder_logging" ("md5sum");
CREATE INDEX "#__finder_logging_idx_searchterm" on "#__finder_logging" ("searchterm");
