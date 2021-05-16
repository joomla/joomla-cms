INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_extension_finder', 'plugin', 'finder', 'extension', 0, 1, 1, 0, '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

TRUNCATE TABLE "#__finder_filters";
ALTER TABLE "#__finder_filters" ALTER COLUMN "created_by" SET DEFAULT 0;
ALTER TABLE "#__finder_filters" ALTER COLUMN "created_by_alias" SET DEFAULT '';
ALTER TABLE "#__finder_filters" ALTER COLUMN "created" DROP DEFAULT;
ALTER TABLE "#__finder_filters" ALTER COLUMN "modified" DROP DEFAULT;
ALTER TABLE "#__finder_filters" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__finder_filters" ALTER COLUMN "checked_out_time" DROP DEFAULT;

TRUNCATE TABLE "#__finder_links";
ALTER TABLE "#__finder_links" ALTER COLUMN "route" TYPE character varying(400);
ALTER TABLE "#__finder_links" ALTER COLUMN "state" SET NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "access" SET NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "language" TYPE character varying(7);
ALTER TABLE "#__finder_links" ALTER COLUMN "language" SET DEFAULT '';
ALTER TABLE "#__finder_links" ALTER COLUMN "indexdate" DROP DEFAULT;
ALTER TABLE "#__finder_links" ALTER COLUMN "publish_start_date" DROP NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "publish_start_date" DROP DEFAULT;
ALTER TABLE "#__finder_links" ALTER COLUMN "publish_end_date" DROP NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "publish_end_date" DROP DEFAULT;
ALTER TABLE "#__finder_links" ALTER COLUMN "start_date" DROP NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "start_date" DROP DEFAULT;
ALTER TABLE "#__finder_links" ALTER COLUMN "end_date" DROP NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "end_date" DROP DEFAULT;
CREATE INDEX "#__finder_links_idx_language" on "#__finder_links" ("language");

CREATE TABLE "#__finder_links_terms" (
	"link_id" bigint NOT NULL,
	"term_id" bigint NOT NULL,
	"weight" REAL NOT NULL DEFAULT 0,
	PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms_idx_term_weight" on "#__finder_links_terms" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms_idx_link_term_weight" on "#__finder_links_terms" ("link_id", "term_id", "weight");

DROP TABLE "#__finder_links_terms0" CASCADE;
DROP TABLE "#__finder_links_terms1" CASCADE;
DROP TABLE "#__finder_links_terms2" CASCADE;
DROP TABLE "#__finder_links_terms3" CASCADE;
DROP TABLE "#__finder_links_terms4" CASCADE;
DROP TABLE "#__finder_links_terms5" CASCADE;
DROP TABLE "#__finder_links_terms6" CASCADE;
DROP TABLE "#__finder_links_terms7" CASCADE;
DROP TABLE "#__finder_links_terms8" CASCADE;
DROP TABLE "#__finder_links_terms9" CASCADE;
DROP TABLE "#__finder_links_termsa" CASCADE;
DROP TABLE "#__finder_links_termsb" CASCADE;
DROP TABLE "#__finder_links_termsc" CASCADE;
DROP TABLE "#__finder_links_termsd" CASCADE;
DROP TABLE "#__finder_links_termse" CASCADE;
DROP TABLE "#__finder_links_termsf" CASCADE;

CREATE TABLE IF NOT EXISTS "#__finder_logging" (
  "searchterm" character varying(255) NOT NULL DEFAULT '',
  "md5sum" character varying(32) NOT NULL DEFAULT '',
  "query" bytea NOT NULL,
  "hits" integer NOT NULL DEFAULT 1,
  "results" integer NOT NULL DEFAULT 0,
  PRIMARY KEY ("md5sum")
);
CREATE INDEX "#__finder_logging_idx_md5sum" on "#__finder_logging" ("md5sum");
CREATE INDEX "#__finder_logging_idx_searchterm" on "#__finder_logging" ("searchterm");

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

TRUNCATE TABLE "#__finder_taxonomy_map";

TRUNCATE TABLE "#__finder_terms";
ALTER TABLE "#__finder_terms" ALTER COLUMN "language" TYPE character varying(7);
ALTER TABLE "#__finder_terms" ALTER COLUMN "language" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "stem" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "soundex" SET DEFAULT '';
CREATE INDEX "#__finder_terms_idx_stem" on "#__finder_terms" ("stem");
CREATE INDEX "#__finder_terms_idx_language" on "#__finder_terms" ("language");
ALTER TABLE "#__finder_terms" DROP CONSTRAINT "#__finder_terms_idx_term";
ALTER TABLE "#__finder_terms" ADD CONSTRAINT "#__finder_terms_idx_term_language" UNIQUE ("term", "language");

DROP TABLE IF EXISTS "#__finder_terms_common";
CREATE TABLE "#__finder_terms_common" (
  "term" varchar(75) NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "custom" integer DEFAULT 0 NOT NULL,
  CONSTRAINT "#__finder_terms_common_idx_term_language" UNIQUE ("term", "language")
);
CREATE INDEX "#__finder_terms_common_idx_lang" on "#__finder_terms_common" ("language");
INSERT INTO "#__finder_terms_common" ("term", "language", "custom") VALUES
	('i', 'en', 0),
	('me', 'en', 0),
	('my', 'en', 0),
	('myself', 'en', 0),
	('we', 'en', 0),
	('our', 'en', 0),
	('ours', 'en', 0),
	('ourselves', 'en', 0),
	('you', 'en', 0),
	('your', 'en', 0),
	('yours', 'en', 0),
	('yourself', 'en', 0),
	('yourselves', 'en', 0),
	('he', 'en', 0),
	('him', 'en', 0),
	('his', 'en', 0),
	('himself', 'en', 0),
	('she', 'en', 0),
	('her', 'en', 0),
	('hers', 'en', 0),
	('herself', 'en', 0),
	('it', 'en', 0),
	('its', 'en', 0),
	('itself', 'en', 0),
	('they', 'en', 0),
	('them', 'en', 0),
	('their', 'en', 0),
	('theirs', 'en', 0),
	('themselves', 'en', 0),
	('what', 'en', 0),
	('which', 'en', 0),
	('who', 'en', 0),
	('whom', 'en', 0),
	('this', 'en', 0),
	('that', 'en', 0),
	('these', 'en', 0),
	('those', 'en', 0),
	('am', 'en', 0),
	('is', 'en', 0),
	('are', 'en', 0),
	('was', 'en', 0),
	('were', 'en', 0),
	('be', 'en', 0),
	('been', 'en', 0),
	('being', 'en', 0),
	('have', 'en', 0),
	('has', 'en', 0),
	('had', 'en', 0),
	('having', 'en', 0),
	('do', 'en', 0),
	('does', 'en', 0),
	('did', 'en', 0),
	('doing', 'en', 0),
	('would', 'en', 0),
	('should', 'en', 0),
	('could', 'en', 0),
	('ought', 'en', 0),
	('i''m', 'en', 0),
	('you''re', 'en', 0),
	('he''s', 'en', 0),
	('she''s', 'en', 0),
	('it''s', 'en', 0),
	('we''re', 'en', 0),
	('they''re', 'en', 0),
	('i''ve', 'en', 0),
	('you''ve', 'en', 0),
	('we''ve', 'en', 0),
	('they''ve', 'en', 0),
	('i''d', 'en', 0),
	('you''d', 'en', 0),
	('he''d', 'en', 0),
	('she''d', 'en', 0),
	('we''d', 'en', 0),
	('they''d', 'en', 0),
	('i''ll', 'en', 0),
	('you''ll', 'en', 0),
	('he''ll', 'en', 0),
	('she''ll', 'en', 0),
	('we''ll', 'en', 0),
	('they''ll', 'en', 0),
	('isn''t', 'en', 0),
	('aren''t', 'en', 0),
	('wasn''t', 'en', 0),
	('weren''t', 'en', 0),
	('hasn''t', 'en', 0),
	('haven''t', 'en', 0),
	('hadn''t', 'en', 0),
	('doesn''t', 'en', 0),
	('don''t', 'en', 0),
	('didn''t', 'en', 0),
	('won''t', 'en', 0),
	('wouldn''t', 'en', 0),
	('shan''t', 'en', 0),
	('shouldn''t', 'en', 0),
	('can''t', 'en', 0),
	('cannot', 'en', 0),
	('couldn''t', 'en', 0),
	('mustn''t', 'en', 0),
	('let''s', 'en', 0),
	('that''s', 'en', 0),
	('who''s', 'en', 0),
	('what''s', 'en', 0),
	('here''s', 'en', 0),
	('there''s', 'en', 0),
	('when''s', 'en', 0),
	('where''s', 'en', 0),
	('why''s', 'en', 0),
	('how''s', 'en', 0),
	('a', 'en', 0),
	('an', 'en', 0),
	('the', 'en', 0),
	('and', 'en', 0),
	('but', 'en', 0),
	('if', 'en', 0),
	('or', 'en', 0),
	('because', 'en', 0),
	('as', 'en', 0),
	('until', 'en', 0),
	('while', 'en', 0),
	('of', 'en', 0),
	('at', 'en', 0),
	('by', 'en', 0),
	('for', 'en', 0),
	('with', 'en', 0),
	('about', 'en', 0),
	('against', 'en', 0),
	('between', 'en', 0),
	('into', 'en', 0),
	('through', 'en', 0),
	('during', 'en', 0),
	('before', 'en', 0),
	('after', 'en', 0),
	('above', 'en', 0),
	('below', 'en', 0),
	('to', 'en', 0),
	('from', 'en', 0),
	('up', 'en', 0),
	('down', 'en', 0),
	('in', 'en', 0),
	('out', 'en', 0),
	('on', 'en', 0),
	('off', 'en', 0),
	('over', 'en', 0),
	('under', 'en', 0),
	('again', 'en', 0),
	('further', 'en', 0),
	('then', 'en', 0),
	('once', 'en', 0),
	('here', 'en', 0),
	('there', 'en', 0),
	('when', 'en', 0),
	('where', 'en', 0),
	('why', 'en', 0),
	('how', 'en', 0),
	('all', 'en', 0),
	('any', 'en', 0),
	('both', 'en', 0),
	('each', 'en', 0),
	('few', 'en', 0),
	('more', 'en', 0),
	('most', 'en', 0),
	('other', 'en', 0),
	('some', 'en', 0),
	('such', 'en', 0),
	('no', 'en', 0),
	('nor', 'en', 0),
	('not', 'en', 0),
	('only', 'en', 0),
	('own', 'en', 0),
	('same', 'en', 0),
	('so', 'en', 0),
	('than', 'en', 0),
	('too', 'en', 0),
	('very', 'en', 0);

TRUNCATE TABLE "#__finder_tokens";
ALTER TABLE "#__finder_tokens" ALTER COLUMN "language" TYPE character varying(7);
ALTER TABLE "#__finder_tokens" ALTER COLUMN "language" SET DEFAULT '';
ALTER TABLE "#__finder_tokens" ALTER COLUMN "stem" SET DEFAULT '';
CREATE INDEX "#__finder_tokens_idx_stem" on "#__finder_tokens" ("stem");
CREATE INDEX "#__finder_tokens_idx_language" on "#__finder_tokens" ("language");

TRUNCATE TABLE "#__finder_tokens_aggregate";
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "language" TYPE character varying(7);
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "language" SET DEFAULT '';
ALTER TABLE "#__finder_tokens_aggregate" DROP COLUMN "map_suffix";
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "stem" SET DEFAULT '';
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "term_weight" SET DEFAULT 0;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "context_weight" SET DEFAULT 0;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "total_weight" SET DEFAULT 0;

ALTER TABLE "#__finder_types" ALTER COLUMN "mime" SET DEFAULT '';
