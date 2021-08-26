-- after 4.0.0 RC1
CREATE TABLE IF NOT EXISTS `#__draft` (
  "article_id" integer unsigned NOT NULL,
  "version_id" integer unsigned NOT NULL,
  "state" smallint NOT NULL DEFAULT 0,
  "hashval" varchar(2083) NOT NULL DEFAULT '',
  "shared_date" timestamp without time zone DEFAULT NULL,
  PRIMARY KEY("article_id", "version_id")
);
ALTER TABLE "#__content" ADD COLUMN "shared" smallint UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE "#__content" ADD COLUMN "draft" smallint UNSIGNED NOT NULL DEFAULT 0;