DROP TABLE "#__finder_taxonomy";

CREATE TABLE IF NOT EXISTS "#__finder_taxonomy" (
  "id" serial NOT NULL,
  "parent_id" integer DEFAULT 0 NOT NULL,
  "lft" integer DEFAULT 0 NOT NULL,
  "rgt" integer DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" VARCHAR(400) NOT NULL DEFAULT '',
  "title" VARCHAR(255) NOT NULL DEFAULT '',
  "alias" VARCHAR(400) NOT NULL DEFAULT '',
  "state" smallint DEFAULT 1 NOT NULL,
  "access" smallint DEFAULT 1 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__finder_taxonomy_state" on "#__finder_taxonomy" ("state");
CREATE INDEX "#__finder_taxonomy_access" on "#__finder_taxonomy" ("access");
CREATE INDEX "#__finder_taxonomy_path" on "#__finder_taxonomy" ("path");
CREATE INDEX "#__finder_taxonomy_lft_rgt" on "#__finder_taxonomy" ("lft", "rgt");
CREATE INDEX "#__finder_taxonomy_alias" on "#__finder_taxonomy" ("alias");
CREATE INDEX "#__finder_taxonomy_language" on "#__finder_taxonomy" ("language");
CREATE INDEX "#__finder_taxonomy_idx_parent_published" on "#__finder_taxonomy" ("parent_id", "state", "access");

INSERT INTO "#__finder_taxonomy" ("id", "parent_id", "lft", "rgt", "level", "path", "title", "alias", "state", "access", "language") VALUES
(1, 0, 0, 1, 0, '', 'ROOT', 'root', 1, 1, '*');

SELECT setval('#__finder_taxonomy_id_seq', 2, false);