CREATE TABLE "#__finder_links_terms" (
	"link_id" bigint NOT NULL,
	"term_id" bigint NOT NULL,
	"weight" REAL NOT NULL,
	PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "idx_term_weight" ("term_id", "weight");
CREATE INDEX "idx_link_term_weight" ("link_id", "term_id", "weight");

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

ALTER TABLE "#__finder_terms"
	ALTER COLUMN "language" TYPE CHAR(7),
	ALTER COLUMN "language" SET DEFAULT '';

ALTER TABLE "#__finder_common"
	ALTER COLUMN "language" TYPE CHAR(7),
	ALTER COLUMN "language" SET DEFAULT '';

ALTER TABLE "#__finder_tokens"
	ALTER COLUMN "language" TYPE CHAR(7),
	ALTER COLUMN "language" SET DEFAULT '';

ALTER TABLE "#__finder_tokens_aggregate"
	ALTER COLUMN "language" TYPE CHAR(7),
	ALTER COLUMN "language" SET DEFAULT '';

ALTER TABLE "#__finder_tokens_aggregate"
	DROP COLUMN "map_suffix";

ALTER TABLE "#__finder_links"
	ALTER COLUMN "language" TYPE CHAR(7),
	ALTER COLUMN "language" SET DEFAULT '';
