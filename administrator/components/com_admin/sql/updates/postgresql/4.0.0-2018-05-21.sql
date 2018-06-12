-- Normalize finder tables default values.
-- finder_filters table
ALTER TABLE "#__finder_filters" ALTER COLUMN "title" SET DEFAULT '';
ALTER TABLE "#__finder_filters" ALTER COLUMN "alias" SET DEFAULT '';
ALTER TABLE "#__finder_filters" ALTER COLUMN "created_by" SET DEFAULT 0;
ALTER TABLE "#__finder_filters" ALTER COLUMN "created_by_alias" SET DEFAULT '';
-- finder_links table
ALTER TABLE "#__finder_links" ALTER COLUMN "url" SET DEFAULT '';
ALTER TABLE "#__finder_links" ALTER COLUMN "route" SET DEFAULT '';
ALTER TABLE "#__finder_links" ALTER COLUMN "state" SET NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "access" SET NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "language" TYPE CHAR(7);
ALTER TABLE "#__finder_links" ALTER COLUMN "language" SET DEFAULT '';
ALTER TABLE "#__finder_links" ALTER COLUMN "type_id" SET DEFAULT 0;
CREATE INDEX "#__finder_links_idx_language" on "#__finder_links" ("language");
-- finder_links_terms tables
ALTER TABLE "#__finder_links_terms" ALTER COLUMN "weight" SET DEFAULT 0;
-- finder_taxonomy table
ALTER TABLE "#__finder_taxonomy" ALTER COLUMN "title" SET DEFAULT '';
-- finder_terms table
ALTER TABLE "#__finder_terms" ALTER COLUMN "term" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "stem" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "soundex" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "language" TYPE CHAR(7);
ALTER TABLE "#__finder_terms" ALTER COLUMN "language" SET DEFAULT '';
CREATE INDEX "#__finder_terms_idx_stem" on "#__finder_terms" ("stem");
CREATE INDEX "#__finder_terms_idx_language" on "#__finder_terms" ("language");
-- finder_terms_common table
ALTER TABLE "#__finder_terms_common" ALTER COLUMN "term" SET DEFAULT '';
ALTER TABLE "#__finder_terms_common" ALTER COLUMN "language" TYPE CHAR(7);
ALTER TABLE "#__finder_terms_common" ALTER COLUMN "language" SET DEFAULT '';
-- finder_tokens table
ALTER TABLE "#__finder_tokens" ALTER COLUMN "term" SET DEFAULT '';
ALTER TABLE "#__finder_tokens" ALTER COLUMN "stem" SET DEFAULT '';
ALTER TABLE "#__finder_tokens" ALTER COLUMN "weight" SET DEFAULT 0;
ALTER TABLE "#__finder_tokens" ALTER COLUMN "language" TYPE CHAR(7);
CREATE INDEX "#__finder_tokens_idx_stem" on "#__finder_tokens" ("stem");
CREATE INDEX "#__finder_tokens_idx_language" on "#__finder_tokens" ("language");
-- finder_tokens_aggregate table
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "term_id" SET DEFAULT 0;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "map_suffix" SET DEFAULT '';
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "term" SET DEFAULT '';
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "stem" SET DEFAULT '';
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "term_weight" SET DEFAULT 0;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "context_weight" SET DEFAULT 0;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "total_weight" SET DEFAULT 0;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "language" TYPE CHAR(7);
-- finder_types table
ALTER TABLE "#__finder_types" ALTER COLUMN "title" SET DEFAULT '';
ALTER TABLE "#__finder_types" ALTER COLUMN "mime" SET DEFAULT '';

